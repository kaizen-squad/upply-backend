<?php

namespace App\Services\Fedapay;

use FedaPay\FedaPay;
use FedaPay\Payout as FedapayPayout;
use FedaPay\Transaction as FedaPayTransaction;
use Exception;

class FedapayService
{
    public function __construct()
    {
        // Set up FedaPay SDKs for authentication
        FedaPay::setApiKey(config('fedapay.secret_key'));
        FedaPay::setEnvironment(config('fedapay.environment'));
    }

    // Function to verify the transaction (collection)
    public function verifyCollect($transactionId)
    {
        try {
            // Verify the status of the transaction in question
            $transaction = FedaPayTransaction::retrieve($transactionId);

            // Check transaction status
            if ($transaction->status !== 'approved') {
                return [
                    'success' => false,
                    'message' => 'Transaction denied',
                    'data' => $transaction
                ];
            }

            return [
                'success' => true,
                'message' => 'Transaction successfully',
                'data' => $transaction
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    // Function to handle FedaPay payout
    public function payout($data)
    {
        try {
            // Create payout
            $createPayout = FedapayPayout::create($data);

            if ($createPayout) {
                return [
                    'success' => true,
                    'message' => 'Payout created',
                    'data' => $createPayout
                ];
            }

            return [
                'success' => false,
                'message' => 'Payout creation failure',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
