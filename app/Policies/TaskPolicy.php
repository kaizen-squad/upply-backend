<?php

namespace App\Policies;

use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function create(User $user): Response
    {
        return ($user->role === UserRole::Client) ? Response::allow() : Response::deny("Vous devez être un client pour créer une mission.");
    }

    public function tasksMine(User $user): Response
    {
        return ($user->role === UserRole::Client) ? Response::allow() : Response::deny("Vous devez être un client pour effectuer cette action.");
    }

    public function update(User $user, Task $task): Response
    {
        return (
            $user->id === $task->client_id
            && $task->status === TaskStatus::OPENED
            && $user->role === UserRole::Client
        ) ? Response::allow() : Response::deny("La mission doit être toujours ouverte. Seul le propriétaire de cette mission peut la modifier.");
    }

    public function delete(User $user, Task $task): Response
    {
        return (
            $user->id === $task->client_id
            && $task->status === TaskStatus::OPENED
            && $user->role === UserRole::Client
        ) ? Response::allow() : Response::deny("La mission doit toujours être ouverte. Seul le propriétaire de cette mission peut la supprimer.");
    }
}
