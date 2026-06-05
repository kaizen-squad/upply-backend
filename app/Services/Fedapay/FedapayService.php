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

        FedaPay::setApiKey($secretKey);
        FedaPay::setEnvironment($environment);
    }

    public function verifyCollect($transactionId)
    {
        Log::info('FedapayService::verifyCollect — début vérification', [
            'fedapay_transaction_id' => $transactionId,
        ]);

        try {
            $transaction = FedaPayTransaction::retrieve($transactionId);

            if ($transaction->status !== 'approved') {
                Log::warning('FedapayService::verifyCollect — transaction non approuvée', [
                    'fedapay_transaction_id' => $transactionId,
                    'status'                 => $transaction->status,
                ]);
                return [
                    'success' => false,
                    'message' => 'Transaction denied',
                    'data'    => $transaction,
                ];
            }

            Log::info('FedapayService::verifyCollect — transaction approuvée', [
                'fedapay_transaction_id' => $transactionId,
                'status'                 => $transaction->status,
            ]);

            return [
                'success' => true,
                'message' => 'Transaction verified successfully',
                'data'    => $transaction,
            ];
        } catch (Exception $e) {
            Log::error('FedapayService::verifyCollect — exception', [
                'fedapay_transaction_id' => $transactionId,
                'error'                  => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'error'   => 'Une erreur est survenue lors de la vérification de la transaction.',
            ];
        }
    }

    public function payout($data)
    {
        $isSandbox = config('fedapay.environment') === 'sandbox';

        Log::info('FedapayService::payout — tentative de création', [
            'amount'      => $data['amount'] ?? null,
            'currency'    => $data['currency'] ?? null,
            'mode'        => $data['mode'] ?? null,
            'environment' => $isSandbox ? 'sandbox' : 'production',
        ]);

        try {
            $createPayout = FedapayPayout::create($data);

            if ($createPayout) {
                Log::info('FedapayService::payout — payout créé avec succès', [
                    'payout_id' => $createPayout->id ?? null,
                    'status'    => $createPayout->status ?? null,
                ]);
                return [
                    'success' => true,
                    'message' => 'Payout created',
                    'data'    => $createPayout,
                ];
            }

            Log::warning('FedapayService::payout — FedaPay a retourné null sans exception');
            return [
                'success' => false,
                'message' => 'Payout creation failure',
            ];

        } catch (Exception $e) {
            $message    = $e->getMessage();
            $httpStatus = method_exists($e, 'getHttpStatus') ? $e->getHttpStatus() : null;

            // SIMULATION SANDBOX : HTTP 500 ou 403 de FedaPay → payout simulé
            $is500 = $httpStatus === 500 || str_contains($message, 'HTTP response code was 500');
            $is403 = $httpStatus === 403 || str_contains($message, 'Opération non autorisée');

            if ($isSandbox && ($is500 || $is403)) {
                $simulatedPayoutId = 'SIMULATED_' . strtoupper(bin2hex(random_bytes(6)));

                Log::warning('FedapayService::payout — [SANDBOX SIMULATION] HTTP ' . ($httpStatus ?? '?') . ' détecté → payout simulé', [
                    'simulated_payout_id' => $simulatedPayoutId,
                    'original_error'      => $message,
                    'amount'              => $data['amount'] ?? null,
                ]);

                $fakePayout         = new \stdClass();
                $fakePayout->id     = $simulatedPayoutId;
                $fakePayout->status = 'scheduled';

                return [
                    'success'   => true,
                    'message'   => 'Payout simulé (sandbox — HTTP 500)',
                    'data'      => $fakePayout,
                    'simulated' => true,
                ];
            }

            Log::error('FedapayService::payout — exception non récupérable', [
                'http_status' => $httpStatus,
                'error'       => $message,
                'amount'      => $data['amount'] ?? null,
                'currency'    => $data['currency'] ?? null,
            ]);

            return [
                'success' => false,
                'error'   => 'Une erreur est survenue lors de la création du paiement.',
            ];
        }
    }

    public function sendPayout($payoutId)
    {
        $isSandbox = config('fedapay.environment') === 'sandbox';

        Log::info('FedapayService::sendPayout — tentative d\'envoi', [
            'payout_id'   => $payoutId,
            'environment' => $isSandbox ? 'sandbox' : 'production',
        ]);

        // Skip real API call for simulated IDs
        if ($isSandbox && str_starts_with((string) $payoutId, 'SIMULATED_')) {
            Log::warning('FedapayService::sendPayout — [SANDBOX SIMULATION] payout_id fictif détecté → envoi simulé', [
                'payout_id' => $payoutId,
            ]);
            return [
                'success' => true,
                'message' => 'Payout simulé envoyé (sandbox)',
                'data'    => null,
            ];
        }

        try {
            $payout = FedapayPayout::retrieve($payoutId);
            $payout->sendNow();

            Log::info('FedapayService::sendPayout — fonds envoyés avec succès', [
                'payout_id' => $payoutId,
                'status'    => $payout->status ?? null,
            ]);

            return [
                'success' => true,
                'message' => 'Payout sent successfully',
                'data'    => $payout,
            ];
        } catch (Exception $e) {
            Log::error('FedapayService::sendPayout — exception', [
                'payout_id' => $payoutId,
                'error'     => $e->getMessage(),
            ]);
            throw $e; // Rethrow pour la gestion du retry dans le Job
        }
    }

    public function getPayoutStatus($payoutId)
    {
        $isSandbox = config('fedapay.environment') === 'sandbox';

        Log::info('FedapayService::getPayoutStatus — récupération statut', [
            'payout_id' => $payoutId,
        ]);

        // Handle simulated IDs in sandbox
        if ($isSandbox && str_starts_with((string) $payoutId, 'SIMULATED_')) {
            Log::warning('FedapayService::getPayoutStatus — [SANDBOX SIMULATION] payout_id fictif détecté → statut simulé "sent"', [
                'payout_id' => $payoutId,
            ]);
            return [
                'success' => true,
                'status'  => 'sent',
                'data'    => null,
            ];
        }

        try {
            $payout = FedapayPayout::retrieve($payoutId);

            Log::info('FedapayService::getPayoutStatus — statut récupéré', [
                'payout_id' => $payoutId,
                'status'    => $payout->status,
            ]);

            return [
                'success' => true,
                'status'  => $payout->status,
                'data'    => $payout,
            ];
        } catch (Exception $e) {
            Log::error('FedapayService::getPayoutStatus — exception', [
                'payout_id' => $payoutId,
                'error'     => $e->getMessage(),
            ]);
            throw $e; // Rethrow pour la logique de réconciliation
        }
    }
}
