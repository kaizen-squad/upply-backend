<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Application;
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
            && $user->role === UserRole::Prestataire
        );
    }

    public function listForTask(User $user, Task $task): bool
    {
        return (
            $user->role === UserRole::Client
            && $user->id == $task->client_id
        );
    }

    public function currentApplication(User $user, Task $task){
        return (
            $user->role === UserRole::Prestataire
            && Application::where('prestataire_id', $user->id)
            ->where('task_id', $task->id)
            ->exists()
        );
    }

    public function listMine(User $user): bool
    {
        return $user->role === UserRole::Prestataire;
    }

    public function accept(User $user, Task $task): bool
    {
        return (
            $user->id === $task->client_id
            && $user->role === UserRole::Client
        );
    }

    public function reject(User $user, Task $task): bool
    {
        return (
            $user->id === $task->client_id
            && $user->role === UserRole::Client
        );
    }
}
