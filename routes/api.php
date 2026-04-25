<?php

use App\Http\Controllers\Api\Auth\AuthenticationController;
use App\Http\Controllers\Api\Fedapay\TransactionController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return new UserResource($request->user());
})->middleware('authentify','role:client');

Route::post('/refresh', [AuthenticationController::class, 'refreshToken']);

Route::post('/register', [AuthenticationController::class, 'register']);

Route::post('/login', [AuthenticationController::class, 'login']);

Route::get('/health', function (Request $request) {
    return [
        'app_name' => config('app.name'),
        'version' => '1.0',
        'health' => 'OK',
    ];
});

Route::middleware('authentify')->group(function (){
    
    Route::get('/logout', [AuthenticationController::class, 'logout']);
    
    Route::post('/tasks/{id}/payment/verify', [TransactionController::class, 'verifyPayment']);
    
    Route::post('/transactions/{transactionId}/payout', [TransactionController::class, 'makePayout']);
    
    Route::post('/fedapay/reconcile', [TransactionController::class, 'triggerReconciliation']);

});

