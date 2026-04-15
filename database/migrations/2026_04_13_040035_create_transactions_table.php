<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('task_id')->constrained('tasks')->onDelete('cascade');
            $table->string('fedapay_transaction_id')->unique()->nullable();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('prestataire_id')->constrained('users')->onDelete('cascade');
            $table->integer('amount_gross');
            $table->integer('commission');
            $table->integer('amount_net');
            $table->string('currency', 3)->default('XOF');
            $table->string('description')->nullable();
            $table->enum('payment_method', ['mobile_money', 'card', 'virement'])->nullable()->default('mobile_money');
            $table->enum('status', ['escrow_lock', 'released', 'failed', 'releasing']);
            $table->timestamp('liberated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
