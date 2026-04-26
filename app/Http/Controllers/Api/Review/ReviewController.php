<?php

namespace App\Http\Controllers\Api\Review;

use App\DTOs\Review\ReviewStoreDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Review\ReviewStoreRequest;
use App\Services\ReviewService;

class ReviewController extends Controller
{
    public function __construct(
        public ReviewService $service
    ){}

    public function note(ReviewStoreRequest $request){
        $user = $request->user();

        $data = ReviewStoreDTO::fromRequest($request);

        $response = $this->service->note($user, $data);

        return response()->json([
            "success" => true,
            "data" => $response,
            "message" => "Review created successfully"
        ], 201);
    }
}
