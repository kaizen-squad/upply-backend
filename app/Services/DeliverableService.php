<?php

namespace App\Services;

use App\DTOs\Deliverable\SubmitDeliverableDTO;
use App\Enums\TaskStatus;
use App\Exceptions\DomainException;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class DeliverableService{
    public function submit(User $prestataire, SubmitDeliverableDTO $data){
        // Check the ability to perform this action
        Gate::authorize('submit');

        $task = Task::findOrFail($data->task_id);

        if($task->status !== TaskStatus::PENDING) throw new DomainException("This task is not waiting for deliverable.");

        
    }
}