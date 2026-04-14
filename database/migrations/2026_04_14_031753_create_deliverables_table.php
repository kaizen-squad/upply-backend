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
<<<<<<< HEAD
            $table->uuid("id")->primary();
            $table->foreignUuid("prestataire_id")->constrained("users");
            $table->foreignUuid("task_id")->constrained()->cascadeOnDelete();
=======
            $table->uuid()->primary();
            $table->foreignId("prestataire_id")->constrained("users");
            $table->foreignId("task_id")->constrained()->cascadeOnDelete();
>>>>>>> db64f77 (feat(deliverables): Set up the deliverables migration)

            $table->text("content");
            $table->string("file_path")->nullable();
            $table->timestamp("submitted_at")->useCurrent();
<<<<<<< HEAD

            $table->softDeletes();
=======
>>>>>>> db64f77 (feat(deliverables): Set up the deliverables migration)
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
