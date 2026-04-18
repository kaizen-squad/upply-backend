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

    public function create(User $user, Task $task): bool
    {
        return (
            $user->id !== $task->client_id
            && $user->role === UserRole::PRESTATAIRE
        );
    }

    public function listForTask(User $user, Task $task): bool
    {
        return (
            $user->status === UserRole::CLIENT
            && $user->id == $task->client_id
        );
    }

    public function listMine(User $user): bool
    {
        return $user->status === UserRole::PRESTATAIRE;
    }

    public function accept(User $user, Task $task): bool
    {
        return (
            $user->id == $task->client_id
            && $user->role === UserRole::CLIENT
        );
    }
}
