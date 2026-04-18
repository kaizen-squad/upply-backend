<?php

namespace App\Services\Fedapay;

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
    public function handleTransaction($transactionId)
    {

        //Get prestataireId with fedapayTransactionId
        $prestataireId = Transaction::where('fedapay_transaction_id', $transactionId)->first()->prestataire_id;

        $verification = $this->fedapayService->verifyCollect($transactionId);
        $clientId = Auth::id();

        DB::beginTransaction();
        try {
            $txData = $verification['data'] ?? null;

            // Check if the transaction is valid
            if ($verification['success'] === false) {
                if ($txData) {
                    $amountGross = (int) ($txData->amount ?? 0);
                    $commission = intdiv($amountGross * 10, 100);
                    $amountNet = $amountGross - $commission;

                    $transaction = Transaction::updateOrCreate(
                        ['fedapay_transaction_id' => $txData->id ?? $transactionId],
                        [
                            'client_id' => $clientId,
                            'prestataire_id' => $prestataireId,
                            'amount_gross' => $amountGross,
                            'commission' => $commission,
                            'amount_net' => $amountNet,
                            'currency' => 'XOF',
                            'payment_method' => $txData->mode ?? 'unknown',
                            'description' => $txData->description ?? null,
                            'status' => 'canceled'
                        ]
                    );

                    TransactionLog::create([
                        'transaction_id' => $transaction->id,
                        'from_status' => null,
                        'to_status' => 'canceled',
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
                'client_id' => $clientId,
                'prestataire_id' => $prestataireId,
                'amount_gross' => $amountGross,
                'commission' => $commission,
                'amount_net' => $amountNet,
                'currency' => 'XOF',
                'payment_method' => $txData->mode ?? 'unknown',
                'description' => $txData->description ?? null,
                'status' => 'escrow_lock',
            ]);
            $transaction->save();

            // Record in the log table only on creation or real status change
            if ($previousStatus === null || $previousStatus !== 'escrow_lock') {
                TransactionLog::create([
                    'transaction_id' => $transaction->id,
                    'from_status' => $previousStatus,
                    'to_status' => 'escrow_lock',
                    'triggered_by' => $clientId,
                    'note' => $txData->description ?? 'Success transaction',
                ]);
            }

            DB::commit();
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
        try {
            $txDetails = DB::transaction(function () use ($transactionId) {
                // Find and lock the transaction with lockForUpdate() to prevent race conditions
                $transaction = Transaction::where('fedapay_transaction_id', $transactionId)
                    ->lockForUpdate()
                    ->first();

                // Check if the transaction exists
                if (!$transaction) {
                    return ['error' => 'Transaction not found'];
                }

                // Check if the transaction is actually locked in escrow
                if ($transaction->status !== 'escrow_lock') {
                    return ['error' => 'Transaction is not active in escrow'];
                }

                // Vérifier la sécurité : la transaction appartient-elle au client ?
                if ($transaction->client_id !== Auth::id()) {
                    return ['error' => 'Payout Unauthorized'];
                }

                // Retrieve prestataire information
                $prestataireInfo = User::find($transaction->prestataire_id);

                if (!$prestataireInfo) {
                    return ['error' => 'Prestataire details not found'];
                }

                if (empty($prestataireInfo->phone)) {
                    return ['error' => 'Le numéro de téléphone du prestataire est manquant.'];
                }

                // Change status to releasing inside DB transaction
                $transaction->update(['status' => 'releasing']);

                TransactionLog::create([
                    'transaction_id' => $transaction->id,
                    'from_status' => 'escrow_lock',
                    'to_status' => 'releasing',
                    'triggered_by' => $transaction->client_id,
                    'note' => 'Initiating payout process'
                ]);

                return [
                    'internal_transaction_id' => $transaction->id,
                    'client_id' => $transaction->client_id,
                    'amount' => $transaction->amount_net,
                    'currency' => $transaction->currency ?? 'XOF',
                    'description' => $transaction->description,
                    'prestataire' => $prestataireInfo
                ];
            });

            if (isset($txDetails['error'])) {
                return ['success' => false, 'message' => $txDetails['error']];
            }

            // Payout configuration (outside DB transaction)
            $data = [
                'amount' => (int) $txDetails['amount'],
                'currency' => ['iso' => $txDetails['currency']],
                'description' => 'Payout for transaction: ' . $txDetails['description'],
                'customer' => [
                    'firstname' => $txDetails['prestataire']->firstname,
                    'lastname' => $txDetails['prestataire']->lastname,
                    'email' => $txDetails['prestataire']->email,
                    'phone_number' => [
                        'number' => $txDetails['prestataire']->phone,
                        'country' => $txDetails['prestataire']->country ?? 'BJ'  // FedaPay country code
                    ]
                ]
            ];

            // Trigger the payout now
            $payout = $this->fedapayService->payout($data);

            // Check if the payout was successfully prepared
            if ($payout['success'] === true && isset($payout['data'])) {
                try {
                    // Actually send the funds using the FedaPay\Payout object
                    $payout['data']->sendNow();

                    // Update the transaction status to released — guard on 'releasing' to prevent overwriting a newer state
                    $affectedRows = Transaction::where('fedapay_transaction_id', $transactionId)
                        ->where('status', 'releasing')
                        ->update([
                            'status' => 'released',
                            'liberated_at' => now(),
                        ]);

                    if ($affectedRows === 0) {
                        Log::warning('Released update skipped: transaction was not in releasing state.', [
                            'transaction_id' => $transactionId,
                        ]);
                    }

                    TransactionLog::create([
                        'transaction_id' => $txDetails['internal_transaction_id'],
                        'from_status' => 'releasing',
                        'to_status' => 'released',
                        'triggered_by' => $txDetails['client_id'],
                        'note' => 'Payout successfully processed'
                    ]);

                    return ['success' => true, 'message' => 'Payout successfully processed'];
                } catch (Exception $e) {
                    Log::error('Payout execution failed: ' . $e->getMessage(), ['transaction_id' => $transactionId]);
                    Transaction::where('fedapay_transaction_id', $transactionId)->update(['status' => 'escrow_lock']);

                    TransactionLog::create([
                        'transaction_id' => $txDetails['internal_transaction_id'],
                        'from_status' => 'releasing',
                        'to_status' => 'escrow_lock',
                        'triggered_by' => $txDetails['client_id'],
                        'note' => 'Payout execution failed'
                    ]);

                    return ['success' => false, 'error' => 'Une erreur est survenue lors de la finalisation du transfert.'];
                }
            }

            Transaction::where('fedapay_transaction_id', $transactionId)->update(['status' => 'escrow_lock']);

            TransactionLog::create([
                'transaction_id' => $txDetails['internal_transaction_id'],
                'from_status' => 'releasing',
                'to_status' => 'escrow_lock',
                'triggered_by' => $txDetails['client_id'],
                'note' => 'Payout preparation failed on FedaPay'
            ]);

            return ['success' => false, 'error' => 'La demande de création du paiement a échouée sur FedaPay.'];
        } catch (Exception $e) {
            Log::error('Release method failed: ' . $e->getMessage(), ['transaction_id' => $transactionId]);

            $updated = Transaction::where('fedapay_transaction_id', $transactionId)
                ->where('status', 'releasing')
                ->update(['status' => 'escrow_lock']);

            if ($updated) {
                // Log the rollback
                $failedTx = Transaction::where('fedapay_transaction_id', $transactionId)->first();
                if ($failedTx) {
                    TransactionLog::create([
                        'transaction_id' => $failedTx->id,
                        'from_status' => 'releasing',
                        'to_status' => 'escrow_lock',
                        'triggered_by' => $failedTx->client_id,
                        'note' => 'Release reverted due to internal error'
                    ]);
                }
            }

            return ['success' => false, 'error' => "Une erreur d'exécution interne est survenue."];
        }
    }
}
