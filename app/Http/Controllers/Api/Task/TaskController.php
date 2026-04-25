<?php

namespace App\Http\Controllers\Api\Task;

use App\DTOs\Task\TaskStoreDTO;
use App\DTOs\Task\TaskUpdateDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\TaskStoreRequest;
use App\Http\Requests\Task\TaskUpdateRequest;
use App\Models\Task;
use App\Services\TaskService;
use Exception;

class TaskController extends Controller{

    public function __construct(
        protected TaskService $service
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
            ], 201);
        }catch(Exception $e){
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], 403);
        }
    }

    public function show(Task $task){
        try{
            $response = $this->service->show($task);

            return response()->json([
                "success" => true,
                "data" => $response,
                "message" => "Task fetch successfully"
            ], 200);
        }catch(Exception $e){
            response()->json([
                "message" => $e->getMessage()
            ], 403);
        }
    }

    public function update(Task $task, TaskUpdateRequest $request){
        $taskData = TaskUpdateDTO::fromRequest($request);

        try{
            $response = $this->service->update($task, $taskData);

            return response()->json([
                "success" => true,
                "message" => "Task updated successfully"
            ], 200);
        }catch(Exception $e){
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], 403);
        }
    }

    public function delete(Task $task){
        $response = $this->service->delete($task);

        if(!$response){
            return response()->json([
                "success" => false,
                "message" => "You don't have the right access to delete this resource."
            ], 403);
        }

        return response()->json([
            "success" => true,
            "message" => "Task deleted successfully"
        ], 200);
    }
}