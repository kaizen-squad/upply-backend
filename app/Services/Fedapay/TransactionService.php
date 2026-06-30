<?php

namespace App\Services\Fedapay;

use App\Enums\ApplicationStatus;
use App\Enums\TaskStatus;
use App\Enums\TransactionStatus;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TransactionService
{
    protected FedapayService $fedapayService;

    public function __construct(FedapayService $fedapayService)
    {
        $this->fedapayService = $fedapayService;
    }

    // Function to save a transaction in the Transaction and TransactionLog tables
    public function handleTransaction($transactionId, $taskId = null)
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

            // 1. Try to extract prestataire_id from metadata first (most reliable if sent)
            if ($txData && isset($txData->custom_metadata)) {
                $metadata = (array) $txData->custom_metadata;
                $prestataireId = $metadata['prestataire_id'] ?? null;
            }

            if ($taskId) {
                // 2. Fallback to finding the accepted application for the task
                if (!$prestataireId) {
                    $task = Task::with(['applications' => function ($query) {
                        $query->where('status', ApplicationStatus::ACCEPTED);
                    }])->find($taskId);

                    if ($task && $task->applications->isNotEmpty()) {
                        $prestataireId = $task->applications->first()->prestataire_id;
                    }
                }

                Task::where('id', $taskId)->update([
                    'status' => TaskStatus::PENDING,
                ]);
            }

            // Fallback for prestataireId if not found via Task reference
            if (!$prestataireId) {
                $existingTx = Transaction::where('fedapay_transaction_id', $transactionId)->first();
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

                    TransactionLog::create([
                        'transaction_id' => $transaction->id,
                        'from_status' => null,
                        'to_status' => TransactionStatus::FAILED->value,
                        'triggered_by' => $clientId,
                        'note' => $txData->description ?? 'Failed transaction'
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
            (new \App\Actions\Transaction\SendPaymentLockedEmails())->handle($transaction);

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
    public function release($transactionId)
    {
        Log::info('TransactionService::release — début du processus de libération', [
            'transaction_id' => $transactionId,
            'triggered_by'   => Auth::id(),
        ]);

        try {
            $txDetails = DB::transaction(function () use ($transactionId) {
                // Find and lock the transaction by its internal id with lockForUpdate() to prevent race conditions
                $transaction = Transaction::where('id', $transactionId)
                    ->lockForUpdate()
                    ->first();

                // Check if the transaction exists
                if (!$transaction) {
                    Log::warning('TransactionService::release — transaction introuvable', [
                        'transaction_id' => $transactionId,
                    ]);
                    return ['error' => 'Transaction not found'];
                }

                Log::info('TransactionService::release — transaction trouvée', [
                    'internal_id' => $transaction->id,
                    'status'      => $transaction->status,
                ]);

                // Check if the transaction is actually locked in escrow
                if ($transaction->status !== TransactionStatus::ESCROW_LOCK) {
                    Log::warning('TransactionService::release — statut invalide pour la libération', [
                        'internal_id' => $transaction->id,
                        'status'      => $transaction->status,
                    ]);
                    return ['error' => 'Transaction is not active in escrow'];
                }

                // Security check: does this transaction belong to the authenticated client?
                if ($transaction->client_id !== Auth::id()) {
                    Log::warning('TransactionService::release — tentative non autorisée', [
                        'internal_id'     => $transaction->id,
                        'transaction_client_id' => $transaction->client_id,
                        'auth_user_id'    => Auth::id(),
                    ]);
                    return ['error' => 'Payout Unauthorized'];
                }

                // Retrieve prestataire information
                $prestataireInfo = User::find($transaction->prestataire_id);

                if (!$prestataireInfo) {
                    Log::error('TransactionService::release — prestataire introuvable', [
                        'internal_id'    => $transaction->id,
                        'prestataire_id' => $transaction->prestataire_id,
                    ]);
                    return ['error' => 'Prestataire details not found'];
                }

                if (empty($prestataireInfo->phone)) {
                    Log::warning('TransactionService::release — numéro de téléphone manquant', [
                        'internal_id'    => $transaction->id,
                        'prestataire_id' => $transaction->prestataire_id,
                    ]);
                    return ['error' => 'Le numéro de téléphone du prestataire est manquant.'];
                }

                // Split the single 'name' field into firstname/lastname for FedaPay
                $nameParts = explode(' ', trim($prestataireInfo->name), 2);
                $prestataireInfo->firstname = $nameParts[0];
                $prestataireInfo->lastname  = $nameParts[1] ?? $nameParts[0];

                // Change status to releasing inside DB transaction
                $transaction->update(['status' => TransactionStatus::RELEASING]);

                TransactionLog::create([
                    'transaction_id' => $transaction->id,
                    'from_status'    => TransactionStatus::ESCROW_LOCK->value,
                    'to_status'      => TransactionStatus::RELEASING->value,
                    'triggered_by'   => $transaction->client_id,
                    'note'           => 'Initiating payout process',
                ]);

                Log::info('TransactionService::release — statut mis à jour vers "releasing"', [
                    'internal_id' => $transaction->id,
                ]);

                return [
                    'internal_transaction_id' => $transaction->id,
                    'task_id'                 => $transaction->task_id,
                    'client_id'               => $transaction->client_id,
                    'amount'                  => $transaction->amount_net,
                    'currency'                => $transaction->currency ?? 'XOF',
                    'description'             => $transaction->description,
                    'payment_method'          => $transaction->payment_method,
                    'prestataire'             => $prestataireInfo,
                ];
            });

            if (isset($txDetails['error'])) {
                Log::error('TransactionService::release — erreur dans la transaction DB', [
                    'fedapay_transaction_id' => $transactionId,
                    'error'                  => $txDetails['error'],
                ]);
                return ['success' => false, 'message' => $txDetails['error']];
            }

            // Resolve the payout mode based on payment method and environment
            $isSandbox     = config('fedapay.environment') === 'sandbox';
            $paymentMethod = $txDetails['payment_method'] ?? 'mobile_money';
            $mode = $isSandbox
                ? (str_contains($paymentMethod, 'card') ? 'card_test' : 'momo_test')
                : (str_contains($paymentMethod, 'card') ? 'card'      : 'mtn');

            Log::info('TransactionService::release — mode payout résolu', [
                'internal_id'    => $txDetails['internal_transaction_id'],
                'mode'           => $mode,
                'payment_method' => $paymentMethod,
                'is_sandbox'     => $isSandbox,
            ]);

            // Payout configuration (outside DB transaction)
            $data = [
                'amount'      => (int) $txDetails['amount'],
                'currency'    => ['iso' => $txDetails['currency']],
                'mode'        => $mode,
                'description' => 'Payout for transaction: ' . $txDetails['description'],
                'customer'    => [
                    'firstname'    => $txDetails['prestataire']->firstname,
                    'lastname'     => $txDetails['prestataire']->lastname,
                    'email'        => $txDetails['prestataire']->email,
                    'phone_number' => [
                        'number'  => $txDetails['prestataire']->phone,
                        'country' => $txDetails['prestataire']->country ?? 'BJ',
                    ],
                ],
            ];

            // Trigger the payout
            $payout = $this->fedapayService->payout($data);

            // Check if the payout was successfully prepared (real or simulated)
            if ($payout['success'] === true && isset($payout['data'])) {
                $payoutId   = $payout['data']->id;
                $simulated  = $payout['simulated'] ?? false;

                // Update the transaction to store the payout ID
                Transaction::where('id', $txDetails['internal_transaction_id'])
                    ->update(['fedapay_payout_id' => $payoutId]);

                // Update task status to VALIDEE immediately to reflect completion in UI
                if (isset($txDetails['task_id'])) {
                    Task::where('id', $txDetails['task_id'])->update(['status' => 'VALIDEE']);
                    Log::info('TransactionService::release — statut de la tâche mis à jour vers "VALIDEE"', [
                        'task_id' => $txDetails['task_id'],
                    ]);
                }

                Log::info('TransactionService::release — payout_id enregistré, dispatch du Job', [
                    'internal_id' => $txDetails['internal_transaction_id'],
                    'payout_id'   => $payoutId,
                    'simulated'   => $simulated,
                ]);

                // Dispatch the Job to handle fund sending and emails
                \App\Jobs\ProcessPayout::dispatch($txDetails['internal_transaction_id']);

                return ['success' => true, 'message' => 'Le transfert est en cours de traitement.'];
            }

            // FedaPay returned a real failure — roll back to escrow_lock
            Log::error('TransactionService::release — création du payout échouée sur FedaPay, rollback vers escrow_lock', [
                'internal_id'    => $txDetails['internal_transaction_id'],
                'transaction_id' => $transactionId,
                'payout_response' => $payout,
            ]);

            Transaction::where('id', $txDetails['internal_transaction_id'])
                ->update(['status' => TransactionStatus::ESCROW_LOCK]);

            TransactionLog::create([
                'transaction_id' => $txDetails['internal_transaction_id'],
                'from_status'    => TransactionStatus::RELEASING->value,
                'to_status'      => TransactionStatus::ESCROW_LOCK->value,
                'triggered_by'   => $txDetails['client_id'],
                'note'           => 'Payout preparation failed on FedaPay — rolled back',
            ]);

            return ['success' => false, 'error' => 'La demande de création du paiement a échouée sur FedaPay.'];

        } catch (Exception $e) {
            Log::error('TransactionService::release — exception non gérée, tentative de rollback', [
                'transaction_id' => $transactionId,
                'error'          => $e->getMessage(),
                'trace'          => $e->getTraceAsString(),
            ]);

            $updated = Transaction::where('id', $transactionId)
                ->where('status', TransactionStatus::RELEASING)
                ->update(['status' => TransactionStatus::ESCROW_LOCK]);

            if ($updated) {
                $failedTx = Transaction::where('id', $transactionId)->first();
                if ($failedTx) {
                    TransactionLog::create([
                        'transaction_id' => $failedTx->id,
                        'from_status'    => TransactionStatus::RELEASING->value,
                        'to_status'      => TransactionStatus::ESCROW_LOCK->value,
                        'triggered_by'   => $failedTx->client_id,
                        'note'           => 'Release reverted due to internal error: ' . $e->getMessage(),
                    ]);
                    Log::info('TransactionService::release — rollback vers escrow_lock effectué', [
                        'internal_id' => $failedTx->id,
                    ]);
                }
            }

            return ['success' => false, 'error' => "Une erreur d'exécution interne est survenue."];
        }
    }
}
