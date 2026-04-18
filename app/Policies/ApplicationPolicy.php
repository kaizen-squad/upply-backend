<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Task;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function create(User $user, Task $task){
        return (
            $user->id !== $task->client_id
            && $user->role === UserRole::PRESTATAIRE
        );
    }

    public function listForTask(User $user, Task $task){
        return (
            $user->status === UserRole::CLIENT
            && $user->id == $task->client_id
        );
    }
}
