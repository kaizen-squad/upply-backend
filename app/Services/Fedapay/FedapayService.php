<?php

namespace App\Services\Fedapay;

use FedaPay\FedaPay;
use FedaPay\Payout as FedapayPayout;
use FedaPay\Transaction as FedaPayTransaction;
use Illuminate\Support\Facades\Log;
use Exception;
use InvalidArgumentException;

class FedapayService
{
    public function __construct()
    {
        $secretKey = config('fedapay.secret_key');
        $environment = config('fedapay.environment');

        if (empty($secretKey) || empty($environment)) {
            throw new InvalidArgumentException('Configuration FedaPay incomplète. Vérifiez votre .env');
        }

        // Set up FedaPay SDKs for authentication
        FedaPay::setApiKey($secretKey);
        FedaPay::setEnvironment($environment);
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
                'message' => 'Transaction verified successfully',
                'data' => $transaction
            ];
        } catch (Exception $e) {
            Log::error('FedaPay verifyCollect exception: ' . $e->getMessage(), ['transaction_id' => $transactionId]);
            return [
                'success' => false,
                'error' => 'Une erreur est survenue lors de la vérification de la transaction.',
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
            // Nettoyage des données PII pour les logs
            $safeData = [
                'amount' => $data['amount'] ?? null,
                'currency' => $data['currency'] ?? null,
            ];
            Log::error('FedaPay payout exception: ' . $e->getMessage(), ['safe_data' => $safeData]);
            return [
                'success' => false,
                'error' => 'Une erreur est survenue lors de la création du paiement.',
            ];
        }
    }

    // Effectue l'envoi effectif des fonds via l'objet Payout
    public function sendPayout($payoutId)
    {
        try {
            $payout = FedapayPayout::retrieve($payoutId);
            $payout->sendNow();
            return [
                'success' => true,
                'message' => 'Payout sent successfully',
                'data' => $payout
            ];
        } catch (Exception $e) {
            Log::error('FedaPay sendPayout exception: ' . $e->getMessage(), ['payout_id' => $payoutId]);
            throw $e;  // Rethrow pour la gestion du retry dans le Job
        }
    }

    // Récupère le statut d'un payout
    public function getPayoutStatus($payoutId)
    {
        try {
            $payout = FedapayPayout::retrieve($payoutId);
            return [
                'success' => true,
                'status' => $payout->status,
                'data' => $payout
            ];
        } catch (Exception $e) {
            Log::error('FedaPay getPayoutStatus exception: ' . $e->getMessage(), ['payout_id' => $payoutId]);
            throw $e;  // Rethrow pour la logique de réconciliation
        }
    }
}
