<?php

namespace App\Services;

use App\Http\Resources\TaskResource;
use App\Models\Task;

class TaskService{

    public function index(){
        $tasks = Task::limit(10)->with('client')->get();

        return TaskResource::collection($tasks);
    }
<<<<<<< HEAD

    public function create(){
        //
    }
=======
>>>>>>> 12b565f (Ended the cherry pick)
}