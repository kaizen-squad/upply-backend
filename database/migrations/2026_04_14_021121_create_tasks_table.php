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
            $table->uuid("id")->primary();
=======
            $table->uuid()->primary();
>>>>>>> d06817f (fix(migrations): Changed id foreign key to uuid foreign key)
=======
            $table->uuid("id")->primary();
>>>>>>> f5b2ff4 (fix(migrations): Added id column name - Removed min constraint on review rating column)
            $table->foreignUuid("client_id")->constrained("users")->cascadeOnDelete();
            
            $table->string("title");
            $table->text("description");
            $table->integer("budget")->index();
            $table->date("deadline");
<<<<<<< HEAD
<<<<<<< HEAD
            $table->enum("status", ["OUVERTE", "EN_COURS", "LIVREE", "VALIDEE"])->index();
            $table->timestamps();
<<<<<<< HEAD
            
=======
>>>>>>> db64f77 (feat(deliverables): Set up the deliverables migration)
=======
            $table->enum("status", ["opened", "on_going", "delivered", "validated"])->index();
=======
            $table->enum("status", ["OUVERTE", "EN_COURS", "LIVREE", "VALIDEE"])->index();
>>>>>>> f5b2ff4 (fix(migrations): Added id column name - Removed min constraint on review rating column)
            $table->timestamps();
            
>>>>>>> 1d59015 (feat(migrations): Added index for optimized query)
            $table->softDeletes();
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
