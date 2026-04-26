<?php

namespace App\DTOs\Review;

use App\Http\Requests\Review\ReviewStoreRequest;

readonly class ReviewStoreDTO{
    public function __construct(
        public int $rating,
        public ?string $comment,
        public string $revieweeId
    ){}

    public static function fromRequest(ReviewStoreRequest $request): self
    {
        $data = $request->validated();

        return new self(
            rating: $data["rating"],
            comment: $data['comment'] ?? null,
            revieweeId: $data['reviewee_id']
        );
    }
}