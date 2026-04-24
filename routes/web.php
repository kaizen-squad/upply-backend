<?php

require __DIR__ . '/api.php';

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\TestEscrowController;

Route::get('/test-escrow', [TestEscrowController::class, 'index'])->name('test.escrow');
Route::get('/test-payout/{transactionId}', [TestEscrowController::class, 'payoutPage'])->name('test.payout');
