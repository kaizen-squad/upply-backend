<?php

namespace App\Services;

use App\DTOs\Task\TaskStoreDTO;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;

class TaskService{

    public function index(){
        $tasks = Task::limit(10)->with('client')->get();

        return TaskResource::collection($tasks);
    }

    public function create(User $client, TaskStoreDTO $newTask){
        
    }
}