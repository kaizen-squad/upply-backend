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
    public function handleTransaction($transactionId, $prestataireId)
    {
        $verification = $this->fedapayService->verifyCollect($transactionId);
        $clientId = Auth::id();

        DB::beginTransaction();
        try {
            $txData = $verification['data'] ?? null;

            // Check if the transaction is valid
            if ($verification['success'] === false) {
                if ($txData) {
                    TransactionLog::create([
                        'transaction_id' => $txData->id ?? $transactionId,
                        'client_id' => $clientId,
                        'prestataire_id' => $prestataireId,
                        'description' => $txData->description ?? 'Failed transaction',
                        'status' => $txData->status ?? 'failed',
                        'metadata' => $txData->custom_metadata
                    ]);

                    Transaction::updateOrCreate(
                        ['transaction_id' => $txData->id ?? $transactionId],
                        [
                            'client_id' => $clientId,
                            'prestataire_id' => $prestataireId,
                            'amount' => $txData->amount ?? 0,
                            'currency' => 'XOF',
                            'payment_method' => $txData->mode ?? 'unknown',
                            'description' => $txData->description ?? null,
                            'status' => 'canceled'
                        ]
                    );
                }

                DB::commit();
                return ['success' => false, 'message' => 'Transaction declined or failed'];
            }

            // Record in the log table
            TransactionLog::create([
                'transaction_id' => $txData->id ?? $transactionId,
                'client_id' => $clientId,
                'prestataire_id' => $prestataireId,
                'description' => $txData->description ?? 'Success transaction',
                'status' => $txData->status ?? 'approved',
                'metadata' => $txData->custom_metadata
            ]);

            // Save the transaction (Escrow)
            Transaction::updateOrCreate(
                ['transaction_id' => $txData->id ?? $transactionId],
                [
                    'client_id' => $clientId,
                    'prestataire_id' => $prestataireId,
                    'amount' => $txData->amount,
                    'currency' => 'XOF',
                    'payment_method' => $txData->mode ?? 'unknown',
                    'description' => $txData->description ?? null,
                    'status' => 'escrow_lock'
                ]
            );

            DB::commit();
            return ['success' => true, 'message' => 'Transaction created successfully and locked'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Function to release escrow funds to the user's number
    public function release($transactionId)
    {
        try {
            $txDetails = DB::transaction(function () use ($transactionId) {
                // Find and lock the transaction with lockForUpdate() to prevent race conditions
                $transaction = Transaction::where('transaction_id', $transactionId)
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
                    'transaction_id' => $transactionId,
                    'client_id' => $transaction->client_id,
                    'prestataire_id' => $transaction->prestataire_id,
                    'description' => 'Initiating payout process',
                    'status' => 'releasing',
                    'metadata' => null
                ]);

                return [
                    'client_id' => $transaction->client_id,
                    'amount' => $transaction->amount,
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

                    // Update the transaction status to released
                    Transaction::where('transaction_id', $transactionId)->update(['status' => 'released']);

                    TransactionLog::create([
                        'transaction_id' => $transactionId,
                        'client_id' => $txDetails['client_id'],
                        'prestataire_id' => $txDetails['prestataire']->id,
                        'description' => 'Payout successfully processed',
                        'status' => 'released',
                        'metadata' => null
                    ]);

                    return ['success' => true, 'message' => 'Payout successfully processed'];
                } catch (Exception $e) {
                    Log::error('Payout execution failed: ' . $e->getMessage(), ['transaction_id' => $transactionId]);
                    Transaction::where('transaction_id', $transactionId)->update(['status' => 'escrow_lock']);
                    
                    TransactionLog::create([
                        'transaction_id' => $transactionId,
                        'client_id' => $txDetails['client_id'],
                        'prestataire_id' => $txDetails['prestataire']->id,
                        'description' => 'Payout execution failed',
                        'status' => 'escrow_lock',
                        'metadata' => null
                    ]);
                    
                    return ['success' => false, 'error' => 'Une erreur est survenue lors de la finalisation du transfert.'];
                }
            }

            Transaction::where('transaction_id', $transactionId)->update(['status' => 'escrow_lock']);
            
            TransactionLog::create([
                'transaction_id' => $transactionId,
                'client_id' => $txDetails['client_id'],
                'prestataire_id' => $txDetails['prestataire']->id,
                'description' => 'Payout preparation failed on FedaPay',
                'status' => 'escrow_lock',
                'metadata' => null
            ]);
            
            return ['success' => false, 'error' => 'La demande de création du paiement a échouée sur FedaPay.'];

        } catch (Exception $e) {
            Log::error('Release method failed: ' . $e->getMessage(), ['transaction_id' => $transactionId]);
            
            $updated = Transaction::where('transaction_id', $transactionId)
                ->where('status', 'releasing')
                ->update(['status' => 'escrow_lock']);
                
            if ($updated) {
                // Log the rollback
                $failedTx = Transaction::where('transaction_id', $transactionId)->first();
                if ($failedTx) {
                    TransactionLog::create([
                        'transaction_id' => $transactionId,
                        'client_id' => $failedTx->client_id,
                        'prestataire_id' => $failedTx->prestataire_id,
                        'description' => 'Release reverted due to internal error',
                        'status' => 'escrow_lock',
                        'metadata' => null
                    ]);
                }
            }
            
            return ['success' => false, 'error' => 'Une erreur d\'exécution interne est survenue.'];
        }
    }
}
