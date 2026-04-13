<?php

namespace App\Services\Fedapay;

use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
                        'metadata' => json_encode($txData->custom_metadata ?? [])
                    ]);

                    Transaction::updateOrCreate(
                        ['transaction_id' => $txData->id ?? $transactionId],
                        [
                            'user_id' => $clientId,
                            'prestataire_id' => $prestataireId,
                            'amount' => $txData->amount ?? 0,
                            'currency' => 'XOF',
                            'mode_payment' => $txData->mode ?? 'unknown',
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
                'metadata' => json_encode($txData->custom_metadata ?? [])
            ]);

            // Save the transaction (Escrow)
            Transaction::updateOrCreate(
                ['transaction_id' => $txData->id ?? $transactionId],
                [
                    'user_id' => $clientId,
                    'prestataire_id' => $prestataireId,
                    'amount' => $txData->amount,
                    'currency' => 'XOF',
                    'mode_payment' => $txData->mode ?? 'unknown',
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
            return DB::transaction(function () use ($transactionId) {
                // Find and lock the transaction with lockForUpdate() to prevent race conditions
                $transaction = Transaction::where('transaction_id', $transactionId)
                    ->lockForUpdate()
                    ->first();

                // Check if the transaction exists
                if (!$transaction) {
                    return ['success' => false, 'message' => 'Transaction not found'];
                }

                // Check if the transaction is actually locked in escrow
                if ($transaction->status !== 'escrow_lock') {
                    return ['success' => false, 'message' => 'Transaction is not active in escrow'];
                }

                // Retrieve vendor (prestataire) information
                // Use find() with the primary ID, or where('user_id', ...)->first() depending on your schema
                $prestataireInfo = User::find($transaction->prestataire_id);

                if (!$prestataireInfo) {
                    return ['success' => false, 'message' => 'Prestataire details not found'];
                }

                // Payout configuration (The amount comes from the database record, not from client-side modifiable variables)
                $data = [
                    'amount' => (int) $transaction->amount,
                    'currency' => ['iso' => $transaction->currency ?? 'XOF'],
                    'description' => 'Payout for transaction: ' . $transaction->description,
                    'customer' => [
                        'firstname' => $prestataireInfo->firstname,
                        'lastname' => $prestataireInfo->lastname,
                        'email' => $prestataireInfo->email,
                        'phone_number' => [
                            'number' => $prestataireInfo->phone ?? '',
                            'country' => $prestataireInfo->country ?? 'BJ'  // FedaPay country code
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
                        $transaction->update(['status' => 'released']);

                        return ['success' => true, 'message' => 'Payout successfully processed'];
                    } catch (Exception $e) {
                        return ['success' => false, 'error' => 'Payout execution failed: ' . $e->getMessage()];
                    }
                }

                return ['success' => false, 'error' => 'Payout creation request failed on Fedapay'];
            });
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Release method failed: ' . $e->getMessage()];
        }
    }
}
