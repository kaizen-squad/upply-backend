<?php

namespace App\Http\Controllers\Api\Fedapay;

use App\Http\Controllers\Controller;
use App\Services\Fedapay\TransactionService;
use Illuminate\Http\JsonResponse;
use Exception;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    public function verifyPayment($id, $prestataireId): JsonResponse
    {
        try {
            $result = $this->transactionService->handleTransaction($id, $prestataireId);

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
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la vérification',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function makePaout(){
        
    }
}
