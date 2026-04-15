<?php

use App\Jobs\ProcessPayoutReconciliation;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Reconcile stuck 'releasing' transactions every 15 minutes
Schedule::job(new ProcessPayoutReconciliation)->everyFifteenMinutes();
