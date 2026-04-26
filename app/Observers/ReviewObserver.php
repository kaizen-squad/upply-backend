<?php

namespace App\Observers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReviewObserver
{
    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        DB::transaction(function() use ($review) {
            $reviewee = User::where('id', $review->reviewee_id)->lockForUpdate()->first();

            $ratingAvg = (($reviewee->rating_avg * $reviewee->rating_count + $review->rating) / ($reviewee->rating_count + 1));

            $reviewee->rating_count++;
            $reviewee->rating_avg = $ratingAvg;

            $reviewee->save();
        });
    }

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review): void
    {
        //
    }

    /**
     * Handle the Review "deleted" event.
     */
    public function deleted(Review $review): void
    {
        //
    }

    /**
     * Handle the Review "restored" event.
     */
    public function restored(Review $review): void
    {
        //
    }

    /**
     * Handle the Review "force deleted" event.
     */
    public function forceDeleted(Review $review): void
    {
        //
    }
}
