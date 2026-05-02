<?php

namespace App\Services;

use App\DTOs\Task\TaskStoreDTO;
use App\DTOs\Task\TaskUpdateDTO;
use App\Enums\TaskStatus;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class TaskService{

    public function index(){
        $tasks = Task::limit(10)->with('client')->where('status', TaskStatus::OPENED)->get();

        return TaskResource::collection($tasks);
    }

    public function create(User $client, TaskStoreDTO $newTask){
        // Check the ability to create a task.
        Gate::authorize('create', Task::class);

        // Create the Task.
        $createdTask = Task::create([
            'title' => $newTask->title,
            'description' => $newTask->description,
            'budget' => $newTask->budget,
            'deadline' => $newTask->deadline,
            'status' => TaskStatus::OPENED,

            'client_id' => $client->id
        ]);

        return new TaskResource($createdTask);
    }

    public function show(Task $task){
        return new TaskResource($task->load('client'));
    }

    public function update(Task $targetTask, TaskUpdateDTO $data): bool
    {
        // Check the ability of the user to update this task.
        Gate::authorize('update', $targetTask);

        $isUpdated = $targetTask->update($data->toArray());

        return $isUpdated;
    }

    public function delete(Task $task): bool
    {
        Gate::authorize('delete', $task);

        return $task->delete();
    }
}