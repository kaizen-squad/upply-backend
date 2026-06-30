<?php

namespace App\Http\Controllers\Api\Fedapay;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPayoutReconciliation;
use App\Services\Fedapay\TransactionService;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    public function verifyPayment(\Illuminate\Http\Request $request, $id): JsonResponse
    {
        $transactionId = $request->input('transaction_id');
        $result = $this->transactionService->handleTransaction($transactionId, $id);

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
    }

    public function makePayout($transactionId): JsonResponse
    {
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
            'data' => $payout['message'] ?? ($payout['error'] ?? 'Unknown error')
        ], 400);
    }

    public function triggerReconciliation(): JsonResponse
    {
        ProcessPayoutReconciliation::dispatch();

        return response()->json([
            'success' => true,
            'message' => 'Le job de réconciliation a été lancé.'
        ], 200);
    }
}
