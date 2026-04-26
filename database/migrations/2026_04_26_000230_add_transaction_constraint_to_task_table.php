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
        Schema::table('tasks', function (Blueprint $table) {
<<<<<<< HEAD
<<<<<<< HEAD
            $table->foreignUuid('transaction_id')->constrained();
=======
            $table->foreignId('transaction_id')->constrained();
>>>>>>> 1a96ffa (fix(endpoint): Added the deliverable validation endpoint)
=======
            $table->foreignUuid('transaction_id')->constrained();
>>>>>>> 6c6097f (feat(reviews): Tested note endpoints)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign('transaction_id');
            $table->dropColumn('transaction_id');
        });
    }
};
