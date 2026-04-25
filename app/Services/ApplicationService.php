<?php

namespace App\Services;

use App\DTOs\Application\ApplicationStoreDTO;
use App\Enums\ApplicationStatus;
use App\Enums\TaskStatus;
use App\Exceptions\DomainException;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\TaskResource;
use App\Models\Application;
use App\Models\Contract;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ApplicationService{
    public function apply(User $prestataire, ApplicationStoreDTO $data){
        // Check first if the task exist.
        $task = Task::findOrFail($data->task_id);

        // Check if the user has the ability to perform this action.
        Gate::authorize('create', [Application::class, $task]);
        
        // Check if the task is opened.
        if($task->status !== TaskStatus::OPENED) throw new DomainException("You can not apply to this task.");

        $newApplication = Application::create([
            'message' => $data->message,
            'status' => ApplicationStatus::PENDING,

            'task_id' => $data->task_id,
            'prestataire_id' => $prestataire->id
        ]);

        return new ApplicationResource($newApplication->load('prestataire'));
    }

    public function listForTask(string $taskId){
        $task = Task::findOrFail($taskId);

        Gate::authorize('listForTask', [Application::class, $task]);

        if($task->status !== TaskStatus::OPENED) throw new DomainException("This task is not opened anymore.");

        $applications = Application::where('task_id', $taskId)->with('prestataire')->get();
    
        return ApplicationResource::collection($applications);
    }

    public function listMine(User $prestataire){
        Gate::authorize('listMine', Application::class);

        $applications = Application::where('prestataire_id', $prestataire->id)->with('task:id,title,description')->get();
    
        return ApplicationResource::collection($applications);
    }

    public function accept(Application $application){
        return DB::transaction(function() use ($application){
            $task = $application->task()->lockForUpdate()->first();

            Gate::authorize('accept', [Application::class, $task]);

            if($application->status !== ApplicationStatus::PENDING) throw new DomainException("This application can not be accepted.");
        
            if($task->status !== TaskStatus::OPENED) throw new DomainException("The current task is already in pending");
        
            // The task move switch to pending
            $task->update([
                'status' => TaskStatus::PENDING
            ]);

            $newContract = Contract::create([
                'application_id' => $application->id
            ]);

            // The application switch to accepted
            $application->update([
                'status' => ApplicationStatus::ACCEPTED,
                'contract_id' => $newContract->id
            ]);

            // We reject all others application for the specific task.
            $task->applications()
                ->where('id', "!=", $application->id)
                ->update([
                    'status' => ApplicationStatus::REJECTED
                ]);

            return new TaskResource($task->load('contract'));
        });
    }

    public function reject(Application $application){
        $targetTask = $application->task;

        Gate::authorize('reject', [Application::class, $targetTask]);

        if($application->status !== ApplicationStatus::PENDING) throw new DomainException("This application can not be rejected.");

        if($targetTask->status !== TaskStatus::OPENED) throw new DomainException("This can is not opened anymore.");

        $isRejcted = $application->update([
            'status' => ApplicationStatus::REJECTED
        ]);

        return $isRejcted;
    }
}