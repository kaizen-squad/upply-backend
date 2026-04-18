<?php

namespace App\Http\Controllers\Api\Application;

use App\DTOs\Application\ApplicationStoreDTO;
use App\Enums\ApplicationStatus;
use App\Enums\TaskStatus;
use App\Exceptions\DomainException;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ApplicationService{
    public function apply(User $prestataire, ApplicationStoreDTO $data){
        // Check first if the task exist.
        $task = Task::FindOrFail($data->task_id);

        // Check if the task is opened.
        if($task->status !== TaskStatus::OPENED) throw new DomainException("Vous ne pouvez pas souscrire à cette tâche.");

        // Check if the user has the ability to perform this action.
        Gate::authorize('create', [Application::class, $task]);

        $newApplication = Application::create([
            'message' => $data->message,
            'status' => ApplicationStatus::PENDING,

            'task_id' => $data->task_id,
            'prestataire_id' => $prestataire->id
        ]);

        return new ApplicationResource($newApplication->load('prestataire'));
    }

    public function listForTask(){

    }
}