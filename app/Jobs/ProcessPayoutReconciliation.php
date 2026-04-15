<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\TransactionLog;
use FedaPay\FedaPay;
use FedaPay\Payout as FedapayPayout;
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
    public function handle(): void
    {
        $timeoutMinutes = config('fedapay.reconciliation_timeout_minutes', 15);

        // Configure FedaPay SDK
        FedaPay::setApiKey(config('fedapay.secret_key'));
        FedaPay::setEnvironment(config('fedapay.environment'));

        $stuckTransactions = Transaction::where('status', 'releasing')
            ->where('updated_at', '<=', now()->subMinutes($timeoutMinutes))
            ->get();

        if ($stuckTransactions->isEmpty()) {
            Log::info('ProcessPayoutReconciliation: no stuck transactions found.');
            return;
        }

        Log::info("ProcessPayoutReconciliation: found {$stuckTransactions->count()} stuck transaction(s).");

        foreach ($stuckTransactions as $transaction) {
            $this->reconcile($transaction);
        }
    }

    private function reconcile(Transaction $transaction): void
    {
        try {
            // Retrieve the payout from FedaPay using the stored transaction ID
            $payout = FedapayPayout::retrieve($transaction->fedapay_transaction_id);
            $fedapayStatus = $payout->status ?? null;

            Log::info("ProcessPayoutReconciliation: transaction [{$transaction->fedapay_transaction_id}] FedaPay status = [{$fedapayStatus}].");

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
                Log::info("ProcessPayoutReconciliation: transaction [{$transaction->fedapay_transaction_id}] still pending ({$fedapayStatus}), will retry next cycle.");
            }
        } catch (Exception $e) {
            Log::error("ProcessPayoutReconciliation: failed to reconcile transaction [{$transaction->fedapay_transaction_id}].", [
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
