<?php

namespace App\Http\Controllers\Api\Fedapay;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPayoutReconciliation;
use App\Services\Fedapay\TransactionService;
use Illuminate\Http\JsonResponse;
use Exception;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    public function verifyPayment($transactionId): JsonResponse
    {
        if (config('app.env') === 'local' && !\Illuminate\Support\Facades\Auth::check()) {
            $client = \App\Models\User::where('role', \App\Enums\UserRole::Client)->first();
            if ($client)
                \Illuminate\Support\Facades\Auth::login($client);
        }
        try {
            $result = $this->transactionService->handleTransaction($transactionId);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Paiement vérifié avec succès',
                    'data' => $result['message']
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Paiement non vérifié',
                'data' => $result['message'] ?? ($result['error'] ?? 'Unknown error')
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la vérification',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function makePayout($transactionId): JsonResponse
    {
        if (config('app.env') === 'local' && !\Illuminate\Support\Facades\Auth::check()) {
            $client = \App\Models\User::where('role', \App\Enums\UserRole::Client)->first();
            if ($client)
                \Illuminate\Support\Facades\Auth::login($client);
        }
        try {
            $payout = $this->transactionService->release($transactionId);
            if ($payout['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transfert initié avec succès',
                    'data' => $payout['message']
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Le transfert a échoué',
                'data' => $payout['message'] ?? 'Unknown error'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du transfert',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function triggerReconciliation(): JsonResponse
    {
        try {
            ProcessPayoutReconciliation::dispatch();

            return response()->json([
                'success' => true,
                'message' => 'Le job de réconciliation a été lancé.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du lancement du job',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
