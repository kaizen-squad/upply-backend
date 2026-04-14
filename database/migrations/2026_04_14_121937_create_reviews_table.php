<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
<<<<<<< HEAD
            $table->uuid("id")->primary();
            $table->foreignUuid("reviewer_id")->constrained("users")->cascadeOnDelete();
            $table->foreignUuid("reviewee_id")->constrained("users");

            $table->uuid("id")->primary();
            $table->foreignUuid("reviewer_id")->constrained("users")->cascadeOnDelete();
            $table->foreignUuid("reviewee_id")->constrained("users");

            $table->smallInteger("rating");
            $table->text("comment")->nullable();
            $table->timestamps();

            $table->softDeletes();
=======
            $table->id();
            $table->foreignId("reviewer_id")->constrained("users")->cascadeOnDelete();
            $table->morphs("reviewee");

            $table->smallInteger("rating")->min();
            $table->text("comment")->nullable();
            $table->timestamps();
>>>>>>> 1d519cf (feat(reviews): Set up the reviews migration)
        });

        DB::statement("ALTER TABLE reviews ADD CONSTRAINT rating_range CHECK(rating >= 1 AND rating <= 5)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
