<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up(): void
    {
        DB::statement("ALTER TABLE transactions 
            DROP CONSTRAINT IF EXISTS transactions_status_check");

        DB::statement("ALTER TABLE transactions 
            ADD CONSTRAINT transactions_status_check 
            CHECK (status IN ('escrow_lock', 'releasing', 'released', 'failed', 'canceled'))");
    }

    public function down(): void
    {
        DB::statement("UPDATE transactions SET status = 'failed' WHERE status = 'canceled'");

        DB::statement("ALTER TABLE transactions 
            DROP CONSTRAINT IF EXISTS transactions_status_check");

        DB::statement("ALTER TABLE transactions 
            ADD CONSTRAINT transactions_status_check 
            CHECK (status IN ('escrow_lock', 'releasing', 'released', 'failed'))");
    }
};