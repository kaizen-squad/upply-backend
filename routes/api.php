<?php

use App\Http\Controllers\Api\Application\ApplicationController;
use App\Http\Controllers\Api\Auth\AuthenticationController;
use App\Http\Controllers\Api\Deliverable\DeliverableController;
use App\Http\Controllers\Api\Fedapay\TransactionController;
use App\Http\Controllers\Api\Task\TaskController;
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

Route::post('/transactions/{transactionId}/payout', [TransactionController::class, 'makePayout'])->middleware('authentify');

Route::post('/fedapay/reconcile', [TransactionController::class, 'triggerReconciliation'])->middleware('authentify');

Route::get('/tasks', [TaskController::class, 'index']);

Route::post('/task/create', [TaskController::class, 'create'])->middleware('authentify');

Route::put('/task/{task}', [TaskController::class, 'update'])->middleware('authentify');

Route::get('/task/{task}', [TaskController::class, 'show']);

Route::delete('/task/{task}', [TaskController::class, 'delete'])->middleware('authentify');

Route::post("/application/apply", [ApplicationController::class, 'apply'])->middleware('authentify');

Route::get("/applications/task", [ApplicationController::class, 'listForTask'])->middleware('authentify');

Route::get("/applications/mine", [ApplicationController::class, 'listMine'])->middleware('authentify');

Route::put("/application/accept/{application}", [ApplicationController::class, 'accept'])->middleware('authentify');

Route::put("/application/reject/{application}", [ApplicationController::class, 'reject'])->middleware('authentify');

Route::post("/deliverable/submit", [DeliverableController::class, 'submit'])->middleware("authentify");

Route::get("/deliverable/{deliverable}", [DeliverableController::class, 'get'])->middleware('authentify');

Route::post("/deliverable/validate/{deliverable}", [DeliverableController::class, 'validate'])->middleware('authentify');
