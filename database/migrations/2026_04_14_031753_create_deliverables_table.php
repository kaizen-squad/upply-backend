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
        Schema::create('deliverables', function (Blueprint $table) {

            $table->uuid("id")->primary();
            $table->foreignUuid("prestataire_id")->constrained("users");
            $table->foreignUuid("task_id")->constrained()->cascadeOnDelete();
            $table->text("content");
            $table->string("file_path")->nullable();
            $table->timestamp("submitted_at")->useCurrent();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliverables');
    }
};
