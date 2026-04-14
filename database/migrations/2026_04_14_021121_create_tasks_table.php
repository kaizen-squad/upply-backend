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
            $table->uuid()->primary();
            $table->foreignId("client_id")->constrained("users")->cascadeOnDelete();
            
            $table->string("title");
            $table->text("description");
            $table->integer("budget");
            $table->date("deadline");
            $table->enum("status", ["opened", "on_going", "delivered", "validated"]);
            $table->timestamps();
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
