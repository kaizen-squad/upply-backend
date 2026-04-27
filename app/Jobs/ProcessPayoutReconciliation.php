<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\TransactionLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessPayoutReconciliation implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the reconciliation job.
     *
     * Finds all transactions stuck in 'releasing' status beyond the configured
     * timeout, queries FedaPay to determine the real payout state, and
     * transitions the transaction to either 'released' or 'escrow_lock'.
     */
    public function handle(\App\Services\Fedapay\FedapayService $fedapayService, \App\Actions\Transaction\SendPayoutConfirmationEmails $emailAction): void
    {
        $timeoutMinutes = config('fedapay.reconciliation_timeout_minutes', 15);

        $stuckTransactions = Transaction::where('status', 'releasing')
            ->where('updated_at', '<=', now()->subMinutes($timeoutMinutes))
            ->get();

        if ($stuckTransactions->isEmpty()) {
            Log::info('ProcessPayoutReconciliation: no stuck transactions found.');
            return;
        }

        Log::info("ProcessPayoutReconciliation: found {$stuckTransactions->count()} stuck transaction(s).");

        foreach ($stuckTransactions as $transaction) {
            $this->reconcile($transaction, $fedapayService, $emailAction);
        }
    }

    private function reconcile(Transaction $transaction, \App\Services\Fedapay\FedapayService $fedapayService, \App\Actions\Transaction\SendPayoutConfirmationEmails $emailAction): void
    {
        try {
            if (empty($transaction->fedapay_payout_id)) {
                Log::warning("ProcessPayoutReconciliation: transaction [{$transaction->id}] has no fedapay_payout_id.");
                return;
            }

            // Retrieve the payout statis from FedaPay
            $fedapayStatus = $fedapayService->getPayoutStatus($transaction->fedapay_payout_id);

            Log::info("ProcessPayoutReconciliation: transaction [{$transaction->id}] FedaPay status = [{$fedapayStatus}].");

            if (in_array($fedapayStatus, ['sent', 'approved'])) {
                // Payout confirmed by FedaPay — mark as released
                $updated = Transaction::where('id', $transaction->id)
                    ->where('status', 'releasing')
                    ->update([
                        'status' => 'released',
                        'liberated_at' => now(),
                    ]);

                if ($updated) {
                    TransactionLog::create([
                        'transaction_id' => $transaction->id,
                        'from_status' => 'releasing',
                        'to_status' => 'released',
                        'triggered_by' => $transaction->client_id,
                        'note' => 'Reconciliation: payout confirmed by FedaPay (status: ' . $fedapayStatus . ')',
                    ]);

                    // begin to send
                    $emailAction->handle($transaction);
                }
            } elseif (in_array($fedapayStatus, ['failed', 'declined', 'cancelled'])) {
                // Payout failed — rollback to escrow_lock
                $updated = Transaction::where('id', $transaction->id)
                    ->where('status', 'releasing')
                    ->update(['status' => 'escrow_lock']);

                if ($updated) {
                    TransactionLog::create([
                        'transaction_id' => $transaction->id,
                        'from_status' => 'releasing',
                        'to_status' => 'escrow_lock',
                        'triggered_by' => $transaction->client_id,
                        'note' => 'Reconciliation: payout failed on FedaPay (status: ' . $fedapayStatus . ')',
                    ]);
                }
            } else {
                // Still pending — log and wait for next cycle
                Log::info("ProcessPayoutReconciliation: transaction [{$transaction->id}] still pending ({$fedapayStatus}), will retry next cycle.");
            }
        } catch (Exception $e) {
            Log::error("ProcessPayoutReconciliation: failed to reconcile transaction [{$transaction->id}].", [
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
