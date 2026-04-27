<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class ProcessPayout implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Nombre maximum de tentatives
     */
    public int $tries = 3;

    /**
     * Backoff exponentiel : 10s, 30s, 90s
     */
    public function backoff(): array
    {
        return [10, 30, 90];
    }

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $transactionId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(\App\Services\Fedapay\FedapayService $fedapayService, \App\Actions\Transaction\SendPayoutConfirmationEmails $emailAction): void
    {
        $transaction = Transaction::find($this->transactionId);

        if (!$transaction || $transaction->status !== 'releasing') {
            Log::warning('ProcessPayout: Transaction not found or not in releasing state.', [
                'transaction_id' => $this->transactionId
            ]);
            return;
        }

        try {
            // Actually send the funds
            $fedapayService->sendPayout($transaction->fedapay_payout_id);

            // Update transaction status
            $transaction->update([
                'status' => 'released',
                'liberated_at' => now(),
            ]);

            // Log status change
            TransactionLog::create([
                'transaction_id' => $transaction->id,
                'from_status' => 'releasing',
                'to_status' => 'released',
                'triggered_by' => $transaction->client_id,
                'note' => 'Payout completed successfully (Job)'
            ]);

            // Send emails
            $emailAction->handle($transaction);
        } catch (Exception $e) {
            Log::warning('ProcessPayout tentative échouée (attempt ' . $this->attempts() . '/' . $this->tries . '): ' . $e->getMessage(), [
                'transaction_id' => $this->transactionId,
            ]);

            // Re-throw pour que Laravel puisse déclencher le retry
            throw $e;
        }
    }

    /**
     * Appelé uniquement après épuisement de toutes les tentatives.
     */
    public function failed(Exception $e): void
    {
        $transaction = Transaction::find($this->transactionId);

        if (!$transaction) {
            Log::error('ProcessPayout::failed — transaction introuvable', [
                'transaction_id' => $this->transactionId,
            ]);
            return;
        }

        Log::error('ProcessPayout Job définitivement échoué après ' . $this->tries . ' tentatives: ' . $e->getMessage(), [
            'transaction_id' => $this->transactionId,
        ]);

        $transaction->update(['status' => 'escrow_lock']);

        TransactionLog::create([
            'transaction_id' => $transaction->id,
            'from_status' => 'releasing',
            'to_status' => 'escrow_lock',
            'triggered_by' => $transaction->client_id,
            'note' => 'Payout job définitivement échoué après ' . $this->tries . ' tentatives: ' . $e->getMessage(),
        ]);
    }
}
