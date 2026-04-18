<?php

namespace App\Services;

use App\DTOs\Task\TaskStoreDTO;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class TaskService{

    public function index(){
        $tasks = Task::limit(10)->with('client')->get();

        return TaskResource::collection($tasks);
    }

    public function create(User $client, TaskStoreDTO $newTask){

        // Check the ability to create a task.
        Gate::authorize('create', Task::class);

        
    }
}