<?php

namespace App\Actions\Transaction;

use App\Mail\Fedapay\PayoutConfirmationClient;
use App\Mail\Fedapay\PayoutConfirmationFreelancer;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class SendPayoutConfirmationEmails
{
    public function handle(Transaction $transaction): void
    {
        try {
            $task = Task::find($transaction->task_id);
            $freelancer = User::find($transaction->prestataire_id);
            $client = User::find($transaction->client_id);

            if ($freelancer && $task) {
                // Generate PDF Receipt
                $pdfData = Pdf::loadView('pdf.receipt', [
                    'transaction_id' => $transaction->fedapay_transaction_id,
                    'task_title' => $task->title,
                    'amount' => $transaction->amount_net,
                    'date' => $transaction->liberated_at,
                    'provider_name' => $freelancer->name,
                ])->output();

                Mail::to($freelancer->email)->send(new PayoutConfirmationFreelancer(
                    $transaction->amount_net,
                    $task->title,
                    $transaction->liberated_at,
                    $pdfData
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
