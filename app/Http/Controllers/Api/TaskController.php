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
            ]);

        }catch(Exception $e){
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        }

    }
}