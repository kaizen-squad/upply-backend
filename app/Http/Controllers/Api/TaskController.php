<?php

namespace App\Http\Controllers\Api;

use App\Services\TaskService;
use Exception;

class TaskController{

    public function __construct(
        public TaskService $service
    ){}

    public function index(){
        try{
            $response = $this->service->index();
            return response()->json([
                "success" => true,
                "data" => $response,
                "message" => "All Tasks"
            ], 200);

        }catch(Exception $e){
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], 400);
        }
    }

    public function create(TaskStoreRequest $request){
        $user = $request->user();

        $taskData = TaskStoreDTO::fromRequest($request);

        try{
            $response = $this->service->create($user, $taskData);

            return response()->json([
                "success" => true,
                "data" => $response,
                "message" => "Task created successfully"
            ], 200);
        }catch(Exception $e){
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], 403);
        }
    }
}