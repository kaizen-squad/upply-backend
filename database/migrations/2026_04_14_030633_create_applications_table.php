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
=======
            $table->uuid()->primary();
<<<<<<< HEAD
            $table->foreignId("task_id")->constrained()->cascadeOnDelete();
            $table->foreignId("prestataire_id")->constrained("users")->cascadeOnDelete();
>>>>>>> 1d59015 (feat(migrations): Added index for optimized query)
=======
            $table->foreignUuid("task_id")->constrained()->cascadeOnDelete();
            $table->foreignUuid("prestataire_id")->constrained("users")->cascadeOnDelete();
>>>>>>> d06817f (fix(migrations): Changed id foreign key to uuid foreign key)

            $table->text("message");
            $table->enum("status", ["EN_ATTENTE", "ACCEPTEE", "REJETEE"]);
            $table->timestamps();
<<<<<<< HEAD
<<<<<<< HEAD

            $table->index(["task_id", "status"]);
=======
>>>>>>> db64f77 (feat(deliverables): Set up the deliverables migration)
=======

            $table->index(["task_id", "status"]);
>>>>>>> 1d59015 (feat(migrations): Added index for optimized query)
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
