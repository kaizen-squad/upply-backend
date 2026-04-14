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
<<<<<<< HEAD
            $table->uuid("id")->primary();
            $table->foreignUuid("task_id")->constrained()->cascadeOnDelete();
            $table->foreignUuid("prestataire_id")->constrained("users")->cascadeOnDelete();
            $table->text("message");
            $table->enum("status", ["EN_ATTENTE", "ACCEPTEE", "REJETEE"]);
            $table->timestamps();
            $table->index(["task_id", "status"]);
            $table->softDeletes();
=======
            $table->uuid()->primary();
            $table->foreignId("task_id")->constrained()->cascadeOnDelete();
            $table->foreignId("prestataire_id")->constrained("users")->cascadeOnDelete();
            
            $table->text("message");
            $table->enum("status", ["pending", "accepted", "rejected"]);
            $table->timestamps();
>>>>>>> d29937a (feat(applications): Set up the application migration)
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
