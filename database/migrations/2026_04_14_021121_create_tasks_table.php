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
        Schema::create('tasks', function (Blueprint $table) {
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD

            $table->uuid("id")->primary();
            $table->foreignUuid("client_id")->constrained("users")->cascadeOnDelete();
=======
            $table->uuid()->primary();
=======
            $table->uuid("id")->primary();
>>>>>>> f5b2ff4 (fix(migrations): Added id column name - Removed min constraint on review rating column)
            $table->foreignUuid("client_id")->constrained("users")->cascadeOnDelete();
            
>>>>>>> d06817f (fix(migrations): Changed id foreign key to uuid foreign key)
            $table->string("title");
            $table->text("description");
            $table->integer("budget")->index();
            $table->date("deadline");
<<<<<<< HEAD
<<<<<<< HEAD
            $table->enum("status", ["OUVERTE", "EN_COURS", "LIVREE", "VALIDEE"])->index();
=======
            $table->enum("status", ["opened", "on_going", "delivered", "validated"])->index();
>>>>>>> 1d59015 (feat(migrations): Added index for optimized query)
=======
            $table->enum("status", ["OUVERTE", "EN_COURS", "LIVREE", "VALIDEE"])->index();
>>>>>>> f5b2ff4 (fix(migrations): Added id column name - Removed min constraint on review rating column)
            $table->timestamps();
            
            $table->softDeletes();
=======
            $table->uuid()->primary();
            $table->foreignUuid("client_id")->constrained("users")->cascadeOnDelete();
            
            $table->string("title");
            $table->text("description");
            $table->integer("budget")->index();
            $table->date("deadline");
            $table->enum("status", ["opened", "on_going", "delivered", "validated"])->index();
            $table->timestamps();
<<<<<<< HEAD
<<<<<<< HEAD
>>>>>>> 896991d (feat(tasks): Set up the task migration)
=======
=======
            
>>>>>>> 1d59015 (feat(migrations): Added index for optimized query)
            $table->softDeletes();
>>>>>>> db64f77 (feat(deliverables): Set up the deliverables migration)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
