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
        Schema::create('contracts', function (Blueprint $table) {
<<<<<<< HEAD
<<<<<<< HEAD
            $table->uuid("id")->primary();
            $table->foreignUuid("application_id")->unique()->constrained()->cascadeOnDelete();
=======
            $table->uuid();
=======
            $table->uuid()->primary();
>>>>>>> fa47f73 (fix(reviews): Added the primary constrained on the uuid)
            $table->foreignId("application_id")->constrained()->cascadeOnDelete();
>>>>>>> 9733822 (feat(contracts): Added contracts migration for application table)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
