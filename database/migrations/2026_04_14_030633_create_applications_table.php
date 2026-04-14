<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignId("task_id")->constrained()->cascadeOnDelete();
            $table->foreignId("prestataire_id")->constrained("users")->cascadeOnDelete();

            $table->text("message");
            $table->enum("status", ["pending", "accepted", "rejected"]);
            $table->timestamps();

            $table->index(["task_id", "status"]);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
