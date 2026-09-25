<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Enums\TransactionStatus;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Services\Fedapay\FedapayService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("payout:{$this->transactionId}"))
                ->releaseAfter(30)
                ->expireAfter(300),
        ];
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
    public function handle(FedapayService $fedapayService): void
    {
        Log::info('ProcessPayout::handle — démarrage du job', [
            'transaction_id' => $this->transactionId,
            'attempt' => $this->attempts(),
            'max_tries' => $this->tries,
        ]);

        $transaction = Transaction::query()->find($this->transactionId);

        if (! $transaction || $transaction->status !== TransactionStatus::RELEASING) {
            Log::warning('ProcessPayout::handle — transaction introuvable ou statut invalide, job annulé', [
                'transaction_id' => $this->transactionId,
                'status' => $transaction?->status,
            ]);

            return;
        }

        try {
            if (empty($transaction->fedapay_payout_id)) {
                throw new Exception('The payout identifier is missing.');
            }

            // Actually send the funds (simulated in sandbox if payout_id starts with SIMULATED_)
            $payoutResult = $fedapayService->sendPayout($transaction->fedapay_payout_id);

            if (($payoutResult['success'] ?? false) !== true) {
                throw new Exception('FedaPay did not confirm the payout.');
            }

            Log::info('ProcessPayout::handle — fonds envoyés (ou simulés), mise à jour vers "released"', [
                'transaction_id' => $this->transactionId,
                'payout_id' => $transaction->fedapay_payout_id,
            ]);

            DB::transaction(function () use ($transaction) {
                $lockedTransaction = Transaction::query()
                    ->whereKey($transaction->id)
                    ->where('status', TransactionStatus::RELEASING)
                    ->lockForUpdate()
                    ->first();

                if ($lockedTransaction === null) {
                    throw new Exception('Transaction status changed before payout completion.');
                }

                $task = null;
                if ($lockedTransaction->task_id) {
                    $task = Task::query()
                        ->whereKey($lockedTransaction->task_id)
                        ->lockForUpdate()
                        ->first();

                    if ($task === null) {
                        throw new Exception('The task associated with this payout no longer exists.');
                    }
                }

                $lockedTransaction->update([
                    'status' => TransactionStatus::RELEASED,
                    'liberated_at' => now(),
                ]);

                if ($task !== null && $task->transaction_id === $lockedTransaction->id) {
                    $task->update(['status' => TaskStatus::VALIDATED]);
                }

                TransactionLog::create([
                    'transaction_id' => $lockedTransaction->id,
                    'from_status' => TransactionStatus::RELEASING->value,
                    'to_status' => TransactionStatus::RELEASED->value,
                    'triggered_by' => $lockedTransaction->client_id,
                    'note' => 'Payout completed successfully (Job)',
                ]);
            });

            Log::info('ProcessPayout::handle — transaction et tâche mises à jour', [
                'transaction_id' => $transaction->id,
                'task_id' => $transaction->task_id,
            ]);

            Log::info('ProcessPayout::handle — statut mis à jour vers "released", dispatch des emails', [
                'transaction_id' => $this->transactionId,
            ]);

            // Send confirmation emails as a separate queued job (decoupled from the payout)
            SendPayoutConfirmationEmailsJob::dispatch($transaction->id);

            Log::info('ProcessPayout::handle — job terminé avec succès', [
                'transaction_id' => $this->transactionId,
                'payout_id' => $transaction->fedapay_payout_id,
            ]);

        } catch (Exception $e) {
            Log::warning('ProcessPayout::handle — tentative échouée', [
                'transaction_id' => $this->transactionId,
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries,
                'error' => $e->getMessage(),
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
        Log::error('ProcessPayout::failed — job définitivement échoué après '.$this->tries.' tentatives', [
            'transaction_id' => $this->transactionId,
            'error' => $e->getMessage(),
        ]);

        $transaction = Transaction::query()->find($this->transactionId);

        if (! $transaction) {
            Log::error('ProcessPayout::failed — transaction introuvable lors du traitement de l\'échec', [
                'transaction_id' => $this->transactionId,
            ]);

            return;
        }

        $transaction->update(['status' => TransactionStatus::FAILED]);

        TransactionLog::create([
            'transaction_id' => $transaction->id,
            'from_status' => TransactionStatus::RELEASING->value,
            'to_status' => TransactionStatus::FAILED->value,
            'triggered_by' => $transaction->client_id,
            'note' => 'Payout job définitivement échoué après '.$this->tries.' tentatives: '.$e->getMessage(),
        ]);

        Log::error('ProcessPayout::failed — transaction marquée en failed', [
            'transaction_id' => $this->transactionId,
        ]);
    }
}
