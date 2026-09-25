<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE transactions\nDROP CONSTRAINT IF EXISTS transactions_status_check");

            DB::statement("ALTER TABLE transactions\nADD CONSTRAINT transactions_status_check\nCHECK (status IN ('escrow_lock', 'releasing', 'released', 'failed', 'canceled'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("UPDATE transactions SET status = 'failed' WHERE status = 'canceled'");

            DB::statement("ALTER TABLE transactions\nDROP CONSTRAINT IF EXISTS transactions_status_check");

            DB::statement("ALTER TABLE transactions\nADD CONSTRAINT transactions_status_check\nCHECK (status IN ('escrow_lock', 'releasing', 'released', 'failed'))");
        }
    }
};
