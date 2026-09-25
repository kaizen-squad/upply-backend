<?php

namespace App\Services;

use App\DTOs\Review\ReviewStoreDTO;
use App\Enums\ApplicationStatus;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Exceptions\DomainException;
use App\Http\Resources\ReviewResource;
use App\Models\Application;
use App\Models\Review;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReviewService
{
    public function create(User $reviewer, ReviewStoreDTO $data, Task $targetTask)
    {
        // We check if the use has the ability to create a review for current task.
        Gate::authorize('create', [Review::class, $targetTask]);

        if ($data->rating < 1 || $data->rating > 5) {
            throw new DomainException('Rating must be between 1 and 5.');
        }

        if ($targetTask->status !== TaskStatus::VALIDATED) {
            throw new DomainException("The current task isn't validated yet !!");
        }

        $hasReview = Review::query()
            ->where('task_id', $targetTask->id)
            ->where('reviewer_id', $reviewer->id)
            ->exists();

        if ($hasReview) {
            throw new DomainException('This task already has a review for this user.');
        }

        if ($reviewer->role === UserRole::Client) {
            $reviewee_id = Application::query()
                ->where('task_id', $targetTask->id)
                ->where('status', ApplicationStatus::ACCEPTED)
                ->value('prestataire_id');

            if ($reviewee_id === null) {
                throw new DomainException('No accepted application was found for this task.');
            }
        } else {
            $reviewee = $targetTask->client;

            if ($reviewee === null) {
                throw new DomainException('The client associated with this task no longer exists.');
            }

            $reviewee_id = $reviewee->id;
        }

        try {
            $newReview = DB::transaction(function () use ($reviewer, $data, $targetTask, $reviewee_id) {
                return Review::create([
                    'reviewer_id' => $reviewer->id,
                    'reviewee_id' => $reviewee_id,
                    'task_id' => $targetTask->id,

                    'rating' => $data->rating,
                    'comment' => $data->comment,
                ]);
            });
        } catch (UniqueConstraintViolationException $e) {
            throw new DomainException('This task already has a review.', previous: $e);
        }

        return new ReviewResource($newReview);
    }

    public function getForTask(Task $targetTask)
    {
        Gate::authorize('viewAny', [Review::class, $targetTask]);

        if ($targetTask->status !== TaskStatus::VALIDATED) {
            throw new DomainException("The current task isn't validated yet !!");
        }

        $reviews = Review::where('task_id', $targetTask->id)->with('reviewer')->get();

        if ($reviews->isEmpty()) {
            throw new DomainException("This task doesn't have any review.");
        }

        return ReviewResource::collection($reviews);
    }
}
