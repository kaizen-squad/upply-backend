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
        Log::info('SendPayoutConfirmationEmails::handle — démarrage', [
            'transaction_id' => $transaction->id,
            'payout_id'      => $transaction->fedapay_payout_id,
        ]);

        try {
            $task       = Task::find($transaction->task_id);
            $freelancer = User::find($transaction->prestataire_id);
            $client     = User::find($transaction->client_id);

            if ($freelancer && $task) {
                // Generate PDF Receipt
                $pdfData = Pdf::loadView('pdf.receipt', [
                    'transaction_id' => $transaction->fedapay_transaction_id,
                    'task_title'     => $task->title,
                    'amount'         => $transaction->amount_net,
                    'date'           => $transaction->liberated_at,
                    'provider_name'  => $freelancer->name,
                ])->output();

                Mail::to($freelancer->email)->send(new PayoutConfirmationFreelancer(
                    $transaction->amount_net,
                    $task->title,
                    $transaction->liberated_at,
                    $pdfData
                ));

                Log::info('SendPayoutConfirmationEmails::handle — email prestataire envoyé', [
                    'transaction_id'  => $transaction->id,
                    'freelancer_email' => $freelancer->email,
                ]);
            } else {
                Log::warning('SendPayoutConfirmationEmails::handle — prestataire ou tâche introuvable, email prestataire ignoré', [
                    'transaction_id'  => $transaction->id,
                    'task_id'         => $transaction->task_id,
                    'prestataire_id'  => $transaction->prestataire_id,
                ]);
            }

            if ($client && $task && $freelancer) {
                Mail::to($client->email)->send(new PayoutConfirmationClient(
                    $task->title,
                    $freelancer->name,
                    $transaction->amount_gross
                ));

                Log::info('SendPayoutConfirmationEmails::handle — email client envoyé', [
                    'transaction_id' => $transaction->id,
                    'client_email'   => $client->email,
                ]);
            } else {
                Log::warning('SendPayoutConfirmationEmails::handle — client introuvable, email client ignoré', [
                    'transaction_id' => $transaction->id,
                    'client_id'      => $transaction->client_id,
                ]);
            }

            Log::info('SendPayoutConfirmationEmails::handle — terminé avec succès', [
                'transaction_id' => $transaction->id,
            ]);

        } catch (Exception $e) {
            Log::error('SendPayoutConfirmationEmails::handle — échec envoi emails', [
                'transaction_id' => $transaction->id,
                'error'          => $e->getMessage(),
            ]);
        }
    }
}
