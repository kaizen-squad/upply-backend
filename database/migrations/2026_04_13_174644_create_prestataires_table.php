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
        Schema::create('prestataires', function (Blueprint $table) {
           $table->id();
           $table->foreignId('user_id')->constrained()->onDelete('cascade');
           $table->string('firstname');
           $table->string('lastname');
           $table->string('job_title')->nullable(); // ex: Développeur Laravel
           $table->text('bio')->nullable();
           $table->integer('daily_rate')->nullable(); // TJM (Tarif Journalier Moyen)
           $table->json('skills')->nullable(); // Utilisation de JSON pour les tags
           $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestataires');
    }
};
