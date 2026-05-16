<?php

namespace App\Services;

use App\DTOs\Review\ReviewStoreDTO;
use App\Enums\TaskStatus;
use App\Exceptions\DomainException;
use App\Http\Resources\ReviewResource;
use App\Models\Application;
use App\Models\Review;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ReviewService{
    public function create(User $reviewer, ReviewStoreDTO $data, Task $targetTask){
        // We check if the use has the ability to create a review for current task.
        Gate::authorize('create', [Review::class, $targetTask]);

        if($targetTask->status !== TaskStatus::VALIDATED) throw new DomainException("The current task isn't validated yet !!");

        $hasReview = Review::where('task_id', $targetTask->id)->exists();
        if($hasReview) throw new DomainException("This task already has a review.");

        $reviewee_id = Application::where('task_id', $targetTask->id)->value("prestataire_id");
        $newReview = Review::create([
            "reviewer_id" => $reviewer->id,
            "reviewee_id" => $reviewee_id,
            "task_id" => $targetTask->id,

            "rating" => $data->rating,
            "comment" => $data->comment
        ]);

        return new ReviewResource($newReview);
    }

    public function getForTask(Task $targetTask){
        if($targetTask->status !== TaskStatus::VALIDATED) throw new DomainException("The current task isn't validated yet !!");

        $hasReview = Review::where('task_id', $targetTask->id)->exists();
        if(!$hasReview) throw new DomainException("This task doesn't have any review.");

        $review = Review::where('task_id', $targetTask->id)->first();

        return new ReviewResource($review->load('reviewer'));
    }
}