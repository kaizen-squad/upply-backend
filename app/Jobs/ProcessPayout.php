<?php

namespace App\Jobs;

use App\Enums\TransactionStatus;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\TransactionLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
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
    public function handle(\App\Services\Fedapay\FedapayService $fedapayService): void
    {
        Log::info('ProcessPayout::handle — démarrage du job', [
            'transaction_id' => $this->transactionId,
            'attempt'        => $this->attempts(),
            'max_tries'      => $this->tries,
        ]);

        $transaction = Transaction::find($this->transactionId);

        if (!$transaction || $transaction->status !== TransactionStatus::RELEASING) {
            Log::warning('ProcessPayout::handle — transaction introuvable ou statut invalide, job annulé', [
                'transaction_id' => $this->transactionId,
                'status'         => $transaction?->status,
            ]);
            return;
        }

        try {
            // Actually send the funds (simulated in sandbox if payout_id starts with SIMULATED_)
            $fedapayService->sendPayout($transaction->fedapay_payout_id);

            Log::info('ProcessPayout::handle — fonds envoyés (ou simulés), mise à jour vers "released"', [
                'transaction_id' => $this->transactionId,
                'payout_id'      => $transaction->fedapay_payout_id,
            ]);

            // Update transaction status
            $transaction->update([
                'status'       => TransactionStatus::RELEASED,
                'liberated_at' => now(),
            ]);

            // Update associated task status to VALIDEE
            if ($transaction->task_id) {
                Task::where('id', $transaction->task_id)->update(['status' => 'VALIDEE']);
                Log::info('ProcessPayout::handle — statut de la tâche mis à jour vers "VALIDEE"', [
                    'task_id' => $transaction->task_id,
                ]);
            }

            // Log status change
            TransactionLog::create([
                'transaction_id' => $transaction->id,
                'from_status'    => TransactionStatus::RELEASING->value,
                'to_status'      => TransactionStatus::RELEASED->value,
                'triggered_by'   => $transaction->client_id,
                'note'           => 'Payout completed successfully (Job)',
            ]);

            Log::info('ProcessPayout::handle — statut mis à jour vers "released", dispatch des emails', [
                'transaction_id' => $this->transactionId,
            ]);

            // Send confirmation emails as a separate queued job (decoupled from the payout)
            \App\Jobs\SendPayoutConfirmationEmailsJob::dispatch($transaction->id);

            Log::info('ProcessPayout::handle — job terminé avec succès', [
                'transaction_id' => $this->transactionId,
                'payout_id'      => $transaction->fedapay_payout_id,
            ]);

        } catch (Exception $e) {
            Log::warning('ProcessPayout::handle — tentative échouée', [
                'transaction_id' => $this->transactionId,
                'attempt'        => $this->attempts(),
                'max_tries'      => $this->tries,
                'error'          => $e->getMessage(),
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
        Log::error('ProcessPayout::failed — job définitivement échoué après ' . $this->tries . ' tentatives', [
            'transaction_id' => $this->transactionId,
            'error'          => $e->getMessage(),
        ]);

        $transaction = Transaction::find($this->transactionId);

        if (!$transaction) {
            Log::error('ProcessPayout::failed — transaction introuvable lors du traitement de l\'échec', [
                'transaction_id' => $this->transactionId,
            ]);
            return;
        }

        $transaction->update(['status' => TransactionStatus::ESCROW_LOCK]);

        TransactionLog::create([
            'transaction_id' => $transaction->id,
            'from_status'    => TransactionStatus::RELEASING->value,
            'to_status'      => TransactionStatus::ESCROW_LOCK->value,
            'triggered_by'   => $transaction->client_id,
            'note'           => 'Payout job définitivement échoué après ' . $this->tries . ' tentatives: ' . $e->getMessage(),
        ]);

        Log::info('ProcessPayout::failed — transaction remise en escrow_lock', [
            'transaction_id' => $this->transactionId,
        ]);
    }
}
