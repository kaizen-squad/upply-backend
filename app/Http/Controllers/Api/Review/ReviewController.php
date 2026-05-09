<?php

namespace App\Http\Controllers\Api\Review;

use App\DTOs\Review\ReviewStoreDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Review\ReviewStoreRequest;
use App\Models\Task;
use App\Services\ReviewService;

class ReviewController extends Controller
{
    public function __construct(
        public ReviewService $service
    ){}

    public function create(Task $task, ReviewStoreRequest $request){
        $data = ReviewStoreDTO::fromRequest($request);
        $user = $request->user();

        $response = $this->service->create($user, $data, $task);

        return response()->json([
            "success" => true,
            "data" => $response,
            "message" => "Task reviewed successfully"
        ], 201);
    }

    public function getForTask(Task $targetTask){
        $response = $this->service->getForTask($targetTask);

        return response()->json([
            "success" => true,
            "data" => $response,
            "message" => "There is the review héhé !!"
        ], 200);
    }
}
