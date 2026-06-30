<?php

namespace App\Jobs;

use App\Actions\Transaction\SendPayoutConfirmationEmails;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class SendPayoutConfirmationEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Nombre maximum de tentatives
     */
    public int $tries = 3;

    /**
     * Backoff exponentiel : 30s, 120s, 300s
     */
    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function __construct(
        protected string $transactionId
    ) {}

    public function handle(SendPayoutConfirmationEmails $emailAction): void
    {
        $transaction = Transaction::find($this->transactionId);

        if (!$transaction) {
            Log::warning('SendPayoutConfirmationEmailsJob::handle — transaction introuvable, job annulé', [
                'transaction_id' => $this->transactionId,
            ]);
            return;
        }

        // L'action relance toute exception : le job pourra retenter puis déclencher failed()
        $emailAction->handle($transaction);
    }

    /**
     * Appelé uniquement après épuisement de toutes les tentatives.
     */
    public function failed(Exception $e): void
    {
        Log::error('SendPayoutConfirmationEmailsJob::failed — emails de confirmation non envoyés après ' . $this->tries . ' tentatives', [
            'transaction_id' => $this->transactionId,
            'error'          => $e->getMessage(),
        ]);
    }
}
