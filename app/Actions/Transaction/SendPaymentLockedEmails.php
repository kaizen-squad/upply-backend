<?php

namespace App\Actions\Transaction;

use App\Mail\Fedapay\PaymentLockedClient;
use App\Mail\Fedapay\PaymentLockedFreelancer;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class SendPaymentLockedEmails
{
    public function handle(Transaction $transaction): void
    {
        Log::info('SendPaymentLockedEmails::handle — démarrage', [
            'transaction_id' => $transaction->id,
            'fedapay_id'     => $transaction->fedapay_transaction_id,
        ]);

        try {
            $task       = Task::find($transaction->task_id);
            $freelancer = User::find($transaction->prestataire_id);
            $client     = User::find($transaction->client_id);

            if ($client && $task && $freelancer) {
                // Send email to Client
                Mail::to($client->email)->send(new PaymentLockedClient(
                    $task->title,
                    $freelancer->name,
                    $transaction->amount_gross
                ));

                Log::info('SendPaymentLockedEmails::handle — email client envoyé', [
                    'transaction_id' => $transaction->id,
                    'client_email'   => $client->email,
                ]);

                // Send email to Freelancer
                Mail::to($freelancer->email)->send(new PaymentLockedFreelancer(
                    $transaction->amount_net,
                    $task->title,
                    $client->name
                ));

                Log::info('SendPaymentLockedEmails::handle — email prestataire envoyé', [
                    'transaction_id'   => $transaction->id,
                    'freelancer_email' => $freelancer->email,
                ]);
            } else {
                Log::warning('SendPaymentLockedEmails::handle — informations manquantes pour l\'envoi des emails', [
                    'transaction_id' => $transaction->id,
                    'has_client'     => !!$client,
                    'has_task'       => !!$task,
                    'has_freelancer' => !!$freelancer,
                ]);
            }

            Log::info('SendPaymentLockedEmails::handle — terminé avec succès', [
                'transaction_id' => $transaction->id,
            ]);
        } catch (Exception $e) {
            Log::error('SendPaymentLockedEmails::handle — échec envoi emails', [
                'transaction_id' => $transaction->id,
                'error'          => $e->getMessage()
            ]);
        }
    }
}
