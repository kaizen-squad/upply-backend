<?php

namespace App\Services;

use App\DTOs\Deliverable\SubmitDeliverableDTO;
use App\Enums\TaskStatus;
use App\Exceptions\DomainException;
use App\Http\Resources\DeliverableResource;
use App\Models\Deliverable;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class DeliverableService{
    public function submit(User $prestataire, SubmitDeliverableDTO $data){
        $task = Task::findOrFail($data->task_id);
        
        // Check the ability to perform this action
        Gate::authorize('submit', $task);


        if($task->status !== TaskStatus::PENDING) throw new DomainException("This task is not waiting for deliverable.");

        $newDeliverable = Deliverable::create([
            'prestataire_id' => $prestataire->id,
            'task_id' => $task->id,

            'content' => $data->content,
            'file_path' => $data->file_path,
            'submitted_at' => now()
        ]);

        $task->update([
            'status' => TaskStatus::DELIVERED
        ]);

        return new DeliverableResource($newDeliverable);
    }

    public function get(Deliverable $deliverable){
        $task = Task::findOrFail($deliverable->task_id);

        Gate::authorize('get', $task);

        if($task->status !== TaskStatus::DELIVERED) throw new DomainException("This task has not yet received any deliverables.");
    
        return new DeliverableResource($deliverable->load('task'));
    }
}