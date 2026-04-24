<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestEscrowController extends Controller
{
    public function index()
    {
        // For testing, if not logged in, log in as the first client
        if (!Auth::check()) {
            $client = User::where('role', UserRole::Client)->first();
            if ($client) {
                Auth::login($client);
            }
        }

        $tasks = Task::with(['applications.prestataire', 'client'])->get();

        return view('test-escrow', compact('tasks'));
    }

    public function payoutPage($transactionId)
    {
        if (!Auth::check()) {
            $client = User::where('role', \App\Enums\UserRole::Client)->first();
            if ($client) {
                Auth::login($client);
            }
        }
        $transaction = Transaction::where('fedapay_transaction_id', $transactionId)->firstOrFail();
        return view('test-payout', compact('transaction'));
    }
}
