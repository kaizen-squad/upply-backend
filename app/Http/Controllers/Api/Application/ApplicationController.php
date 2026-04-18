<?php

namespace App\Http\Controllers\Api\Application;

use App\DTOs\Application\ApplicationStoreDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Application\ApplicationListForTaskRequest;
use App\Http\Requests\Application\ApplicationStoreRequest;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function __construct(
        protected ApplicationService $service
    ){}

    public function apply(ApplicationStoreRequest $request){
        $user = $request->user();
        $data = ApplicationStoreDTO::fromRequest($request);

        $response = $this->service->apply($user, $data);

        return response()->json([
            'success' => true,
            'message' => $response
        ], 201);
    }

    public function listForTask(ApplicationListForTaskRequest $request){
        $response = $this->service->listForTask($request->input('task_id'));

        return response()->json([
            "success" => true,
            "data" => $response,
            "message" => "List of applications for a task"
        ]);
    }

    public function listMine(Request $request){
        $user = $request->user();

        $response = $this->service->listMine($user);

        return response()->json([
            "success" => true,
            "data" => $response,
            "message" => "List of applications for a prestataire"
        ]);
    }
}
