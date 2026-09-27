<?php

namespace App\Services\Fedapay;

use App\Actions\Transaction\SendPaymentLockedEmails;
use App\Enums\ApplicationStatus;
use App\Enums\TaskStatus;
use App\Enums\TransactionStatus;
use App\Exceptions\DomainException;
use App\Exceptions\PayoutRejectedException;
use App\Jobs\ProcessPayout;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TransactionService
{
    private const PAYOUT_REFUSED_STATUSES = ['failed', 'declined', 'cancelled', 'canceled'];

    protected FedapayService $fedapayService;

    public function __construct(FedapayService $fedapayService)
    {
        $this->fedapayService = $fedapayService;
    }

    // Function to save a transaction in the Transaction and TransactionLog tables
    public function handleTransaction(string $transactionId, $taskId = null)
    {
        $verification = $this->fedapayService->verifyCollect($transactionId);
        $clientId = Auth::id();

        DB::beginTransaction();
        try {
            $txData = $verification['data'] ?? null;
            $prestataireId = null;

            // Extract from reference if available, or use provided taskId
            if ($txData && isset($txData->reference)) {
                $reference = (array) $txData->reference;
                $taskId = $reference['task_id'] ?? $taskId;
            }

            // Try to extract prestataire_id from metadata first (most reliable if sent)
            if ($txData && isset($txData->custom_metadata)) {
                $metadata = (array) $txData->custom_metadata;
                $prestataireId = $metadata['prestataire_id'] ?? null;
            }

            if ($taskId) {
                //  Fallback to finding the accepted application for the task
                if (! $prestataireId) {
                    $task = Task::with(['applications' => function ($query) {
                        $query->where('status', ApplicationStatus::ACCEPTED);
                    }])->find($taskId);

                    if ($task && $task->applications->isNotEmpty()) {
                        $prestataireId = $task->applications->first()->prestataire_id;
                    }
                }

                Task::query()->where('id', $taskId)->update([
                    'status' => TaskStatus::PENDING,
                ]);
            }

            // Fallback for prestataireId if not found via Task reference
            if (! $prestataireId) {
                $existingTx = Transaction::query()->where('fedapay_transaction_id', $transactionId)->first();
                $prestataireId = $existingTx?->prestataire_id;
            }

            // Check if the transaction is valid
            if ($verification['success'] === false) {
                if ($txData) {
                    $amountGross = (int) ($txData->amount ?? 0);
                    $commission = intdiv($amountGross * 10, 100);
                    $amountNet = $amountGross - $commission;

                    $transaction = Transaction::updateOrCreate(
                        ['fedapay_transaction_id' => $txData->id ?? $transactionId],
                        [
                            'task_id' => $taskId,
                            'client_id' => $clientId,
                            'prestataire_id' => $prestataireId,
                            'amount_gross' => $amountGross,
                            'commission' => $commission,
                            'amount_net' => $amountNet,
                            'currency' => 'XOF',
                            'payment_method' => str_contains($txData->mode ?? '', 'card') ? 'card' : 'mobile_money',
                            'description' => $txData->description ?? null,
                            'status' => TransactionStatus::FAILED,
                        ]
                    );

                    $task = Task::query()->findOrFail($taskId);
                    $task->transaction_id = $transaction->id;
                    $task->save();

                    TransactionLog::create([
                        'transaction_id' => $transaction->id,
                        'from_status' => null,
                        'to_status' => TransactionStatus::FAILED->value,
                        'triggered_by' => $clientId,
                        'note' => $txData->description ?? 'Failed transaction',
                    ]);
                }

                DB::commit();

                return ['success' => false, 'message' => 'Transaction declined or failed'];
            }

            $amountGross = (int) $txData->amount;
            $commission = intdiv($amountGross * 10, 100);
            $amountNet = $amountGross - $commission;

            // Save the transaction (Escrow) — use firstOrNew to capture previous status for audit log
            $transaction = Transaction::firstOrNew([
                'fedapay_transaction_id' => $txData->id ?? $transactionId,
            ]);
            $previousStatus = $transaction->exists ? $transaction->status : null;
            $transaction->fill([
                'task_id' => $taskId,
                'client_id' => $clientId,
                'prestataire_id' => $prestataireId,
                'amount_gross' => $amountGross,
                'commission' => $commission,
                'amount_net' => $amountNet,
                'currency' => 'XOF',
                'payment_method' => str_contains($txData->mode ?? '', 'card') ? 'card' : 'mobile_money',
                'description' => $txData->description ?? null,
                'status' => TransactionStatus::ESCROW_LOCK,
            ]);
            $transaction->save();

            if ($taskId) {
                Task::query()->whereKey($taskId)->update(['transaction_id' => $transaction->id]);
            }

            // Record in the log table only on creation or real status change
            if ($previousStatus === null || $previousStatus !== TransactionStatus::ESCROW_LOCK) {
                TransactionLog::create([
                    'transaction_id' => $transaction->id,
                    'from_status' => $previousStatus?->value,
                    'to_status' => TransactionStatus::ESCROW_LOCK->value,
                    'triggered_by' => $clientId,
                    'note' => $txData->description ?? 'Success transaction',
                ]);
            }

            DB::commit();

            // Send confirmation emails
            (new SendPaymentLockedEmails)->handle($transaction);

            return ['success' => true, 'message' => 'Transaction created successfully and locked'];
        } catch (Exception $e) {
            DB::rollBack();

            $errorId = bin2hex(random_bytes(8));
            Log::error('Erreur lors du traitement de la transaction FedaPay.', [
                'error_id' => $errorId,
                'transaction_id' => $transactionId,
                'prestataire_id' => $prestataireId,
                'client_id' => $clientId,
                'exception_message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Une erreur interne est survenue lors du traitement de la transaction.',
                'error_id' => $errorId,
            ];
        }
    }

    // Function to release escrow funds to the user's number
    public function release(string $transactionId): array
    {
        $correlationId = bin2hex(random_bytes(8));
        $reachedReleasing = false;

        try {
            $result = DB::transaction(function () use ($transactionId, &$reachedReleasing) {
                // Find and lock the transaction by its internal id with lockForUpdate() to prevent race conditions
                $transaction = Transaction::query()->where('id', $transactionId)
                    ->lockForUpdate()
                    ->first();

                // Check if the transaction exists
                if (! $transaction) {
                    throw new DomainException('Transaction not found');
                }

                // Check if the transaction is actually locked in escrow
                if ($transaction->status !== TransactionStatus::ESCROW_LOCK) {
                    throw new DomainException('Transaction is not active in escrow');
                }

                // Security check: does this transaction belong to the authenticated client?
                if ($transaction->client_id !== Auth::id()) {
                    throw new DomainException('Payout Unauthorized');
                }

                // Escrow may only be released once the work has actually been delivered
                $task = $transaction->task;

                if (! $task) {
                    throw new DomainException('Task not found for this transaction');
                }

                if ($task->status !== TaskStatus::DELIVERED) {
                    throw new DomainException("This task isn't delivered yet.");
                }

                // Retrieve prestataire information
                $prestataireInfo = User::query()->find($transaction->prestataire_id);

                if (! $prestataireInfo) {
                    throw new DomainException('Prestataire details not found');
                }

                if (empty($prestataireInfo->phone)) {
                    throw new DomainException('Le numéro de téléphone du prestataire est manquant.');
                }

                // Split the single 'name' field into firstname/lastname for FedaPay
                $nameParts = explode(' ', trim((string) $prestataireInfo->name), 2);
                $prestataireInfo->firstname = $nameParts[0];
                $prestataireInfo->lastname = $nameParts[1] ?? $nameParts[0];

                // Resolve the payout mode based on payment method and environment
                $isSandbox = config('fedapay.environment') === 'sandbox';
                $paymentMethod = $transaction->payment_method ?? 'mobile_money';
                $mode = $isSandbox
                    ? (str_contains($paymentMethod, 'card') ? 'card_test' : 'momo_test')
                    : (str_contains($paymentMethod, 'card') ? 'card' : 'mtn');

                // Payout configuration
                $data = [
                    'amount' => (int) $transaction->amount_net,
                    'currency' => ['iso' => $transaction->currency ?? 'XOF'],
                    'mode' => $mode,
                    'description' => 'Payout for transaction: '.$transaction->description,
                    'customer' => [
                        'firstname' => $prestataireInfo->firstname,
                        'lastname' => $prestataireInfo->lastname,
                        'email' => $prestataireInfo->email,
                        'phone_number' => [
                            'number' => $prestataireInfo->phone,
                            'country' => $prestataireInfo->country ?? 'BJ',
                        ],
                    ],
                ];

                $transaction->update(['status' => TransactionStatus::RELEASING]);
                $reachedReleasing = true;

                TransactionLog::create([
                    'transaction_id' => $transaction->id,
                    'from_status' => TransactionStatus::ESCROW_LOCK->value,
                    'to_status' => TransactionStatus::RELEASING->value,
                    'triggered_by' => $transaction->client_id,
                    'note' => 'Initiating payout process',
                ]);

                // The FedaPay call happens inside the transaction: a RELEASING state is never
                // committed without its payout id, and any failure rolls back to escrow_lock.
                $payout = $this->fedapayService->payout($data);

                if (($payout['success'] ?? false) !== true || ! isset($payout['data'])) {
                    throw new PayoutRejectedException('La demande de création du paiement a échouée sur FedaPay.');
                }

                $payoutId = $payout['data']->id ?? null;

                if (! is_string($payoutId) || trim($payoutId) === '') {
                    throw new PayoutRejectedException("FedaPay n'a pas retourné d'identifiant de payout.");
                }

                // A payout can be created while already being refused by FedaPay: never dispatch it.
                $payoutStatus = $payout['data']->status ?? null;
                $lastErrorCode = $payout['data']->last_error_code ?? null;

                if (in_array($payoutStatus, self::PAYOUT_REFUSED_STATUSES, true) || ! empty($lastErrorCode)) {
                    throw new PayoutRejectedException(sprintf(
                        'FedaPay a refusé le payout (statut : %s, erreur : %s).',
                        $payoutStatus ?? 'inconnu',
                        $payout['data']->last_error_message ?? 'non précisée'
                    ));
                }

                $updated = Transaction::query()
                    ->whereKey($transaction->id)
                    ->where('status', TransactionStatus::RELEASING)
                    ->update(['fedapay_payout_id' => $payoutId]);

                if ($updated !== 1) {
                    throw new PayoutRejectedException('Transaction status changed before payout dispatch.');
                }

                TransactionLog::create([
                    'transaction_id' => $transaction->id,
                    'from_status' => TransactionStatus::RELEASING->value,
                    'to_status' => TransactionStatus::RELEASING->value,
                    'triggered_by' => $transaction->client_id,
                    'note' => 'Payout '.$payoutId.' accepted by FedaPay'.(($payout['simulated'] ?? false) ? ' (simulated in sandbox)' : ''),
                ]);

                ProcessPayout::dispatch($transaction->id)->afterCommit();

                return [
                    'success' => true,
                    'message' => 'Le transfert est en cours de traitement.',
                    'payout_id' => $payoutId,
                ];
            });

            Log::info('TransactionService::release — payout programmé', [
                'transaction_id' => $transactionId,
                'correlation_id' => $correlationId,
                'payout_id' => $result['payout_id'],
            ]);

            return $result;
        } catch (PayoutRejectedException $e) {
            Log::warning('TransactionService::release — payout refusé par FedaPay', [
                'transaction_id' => $transactionId,
                'correlation_id' => $correlationId,
                'reason' => $e->getMessage(),
            ]);

            $this->recordFailedRelease($transactionId, $reachedReleasing, $e->getMessage());

            return ['success' => false, 'error' => $e->getMessage()];
        } catch (DomainException $e) {
            Log::warning('TransactionService::release — demande rejetée', [
                'transaction_id' => $transactionId,
                'correlation_id' => $correlationId,
                'reason' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        } catch (Throwable $e) {
            Log::error('TransactionService::release — exception non gérée', [
                'transaction_id' => $transactionId,
                'correlation_id' => $correlationId,
                'exception' => $e::class,
                'error' => $e->getMessage(),
            ]);

            $this->recordFailedRelease($transactionId, $reachedReleasing, 'Release reverted due to internal error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => "Une erreur d'exécution interne est survenue.",
                'error_id' => $correlationId,
            ];
        }
    }

    private function recordFailedRelease(string $transactionId, bool $reachedReleasing, string $note): void
    {
        if (! $reachedReleasing) {
            return;
        }

        try {
            DB::transaction(function () use ($transactionId, $note) {
                $transaction = Transaction::query()->whereKey($transactionId)
                    ->lockForUpdate()
                    ->first();

                if (! $transaction || $transaction->status !== TransactionStatus::ESCROW_LOCK) {
                    return;
                }

                TransactionLog::create([
                    'transaction_id' => $transaction->id,
                    'from_status' => TransactionStatus::RELEASING->value,
                    'to_status' => TransactionStatus::ESCROW_LOCK->value,
                    'triggered_by' => Auth::id(),
                    'note' => $note,
                ]);
            });
        } catch (Throwable $e) {
            Log::critical("TransactionService::release — audit de l'échec impossible", [
                'transaction_id' => $transactionId,
                'exception' => $e::class,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
