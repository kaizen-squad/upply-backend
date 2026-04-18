<?php

namespace App\Policies;

use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function create(User $user): bool
    {
        return $user->role == UserRole::CLIENT;
    }

    public function update(User $user, Task $task): bool
    {
        return (
            $user->id == $task->client_id
            && $task->status == TaskStatus::OPENED
            && $user->role == UserRole::CLIENT
        );
    }

    public function delete(User $user, Task $task){
        return (
            $user->id == $task->client_id
            && $task->status == TaskStatus::OPENED
            && $user->role == UserRole::CLIENT
        );
    }
}
