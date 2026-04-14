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
            $table->uuid("id")->primary();
            $table->foreignUuid("client_id")->constrained("users")->cascadeOnDelete();
            
            $table->string("title");
            $table->text("description");
            $table->integer("budget")->index();
            $table->date("deadline");
<<<<<<< HEAD
            $table->enum("status", ["OUVERTE", "EN_COURS", "LIVREE", "VALIDEE"])->index();
            $table->timestamps();
<<<<<<< HEAD
            
=======
>>>>>>> db64f77 (feat(deliverables): Set up the deliverables migration)
=======
            $table->enum("status", ["opened", "on_going", "delivered", "validated"])->index();
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
