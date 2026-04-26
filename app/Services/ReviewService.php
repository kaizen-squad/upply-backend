<?php

namespace App\Services;

use App\DTOs\Review\ReviewStoreDTO;
use App\Models\Review;
use App\Models\User;

class ReviewService{
    public function note(User $reviewerId, ReviewStoreDTO $data){
        $newReview = Review::create([
            "rating" => $data->rating,
            "comment" => $data->comment,

            "reviewer_id" => $reviewerId->id,
            "reviewee_id" => $data->revieweeId
        ]);
    }
}