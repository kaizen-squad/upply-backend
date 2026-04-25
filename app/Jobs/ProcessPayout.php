<?php

namespace App\Jobs;

use App\Mail\Fedapay\PayoutConfirmationClient;
use App\Mail\Fedapay\PayoutConfirmationFreelancer;
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
     * Create a new job instance.
     */
    public function __construct(
        protected string $fedapayTransactionId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Configure FedaPay SDK
        \FedaPay\FedaPay::setApiKey(config('fedapay.secret_key'));
        \FedaPay\FedaPay::setEnvironment(config('fedapay.environment'));

        $transaction = Transaction::where('fedapay_transaction_id', $this->fedapayTransactionId)->first();

        if (!$transaction || $transaction->status !== 'releasing') {
            Log::warning('ProcessPayout: Transaction not found or not in releasing state.', [
                'fedapay_id' => $this->fedapayTransactionId
            ]);
            return;
        }

        try {
            // Retrieve payout object
            $payoutObject = \FedaPay\Payout::retrieve($this->fedapayTransactionId);

            // Actually send the funds
            $payoutObject->sendNow();

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
            $this->sendConfirmationEmails($transaction);
        } catch (Exception $e) {
            Log::error('ProcessPayout Job failed: ' . $e->getMessage(), [
                'fedapay_id' => $this->fedapayTransactionId
            ]);

            $transaction->update(['status' => 'escrow_lock']);

            TransactionLog::create([
                'transaction_id' => $transaction->id,
                'from_status' => 'releasing',
                'to_status' => 'escrow_lock',
                'triggered_by' => $transaction->client_id,
                'note' => 'Payout job failed: ' . $e->getMessage()
            ]);
        }
    }

    protected function sendConfirmationEmails(Transaction $transaction)
    {
        try {
            $task = Task::find($transaction->task_id);
            $freelancer = User::find($transaction->prestataire_id);
            $client = User::find($transaction->client_id);

            if ($freelancer && $task) {
                Mail::to($freelancer->email)->send(new PayoutConfirmationFreelancer(
                    $transaction->amount_net,
                    $task->title,
                    $transaction->liberated_at
                ));
            }

            if ($client && $task && $freelancer) {
                Mail::to($client->email)->send(new PayoutConfirmationClient(
                    $task->title,
                    $freelancer->name,
                    $transaction->amount_gross
                ));
            }
        } catch (Exception $e) {
            Log::error('Failed to send payout confirmation emails', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
