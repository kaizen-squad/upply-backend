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
<<<<<<< HEAD
=======
>>>>>>> f5b2ff4 (fix(migrations): Added id column name - Removed min constraint on review rating column)
            $table->uuid("id")->primary();
            $table->foreignUuid("prestataire_id")->constrained("users");
            $table->foreignUuid("task_id")->constrained()->cascadeOnDelete();
=======
            $table->uuid()->primary();
<<<<<<< HEAD
            $table->foreignId("prestataire_id")->constrained("users");
            $table->foreignId("task_id")->constrained()->cascadeOnDelete();
>>>>>>> db64f77 (feat(deliverables): Set up the deliverables migration)
=======
            $table->foreignUuid("prestataire_id")->constrained("users");
            $table->foreignUuid("task_id")->constrained()->cascadeOnDelete();
>>>>>>> d06817f (fix(migrations): Changed id foreign key to uuid foreign key)

            $table->text("content");
            $table->string("file_path")->nullable();
            $table->timestamp("submitted_at")->useCurrent();
<<<<<<< HEAD
<<<<<<< HEAD

            $table->softDeletes();
=======
>>>>>>> db64f77 (feat(deliverables): Set up the deliverables migration)
=======

            $table->softDeletes();
>>>>>>> 1d59015 (feat(migrations): Added index for optimized query)
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
