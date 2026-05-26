<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ApplicationPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function create(User $user, Task $task): Response
    {
        return (
            $user->id !== $task->client_id
            && $user->role === UserRole::Prestataire
        ) ? Response::allow() : Response::deny("Vous devez être un prestataire pour créer des candidatures.");
    }

    public function listForTask(User $user, Task $task): Response
    {
        return (
            $user->role === UserRole::Client
            && $user->id == $task->client_id
        ) ? Response::allow() : Response::deny("Seul le client propriétaire de cette mission peut voir les candidatures associées.");
    }

    public function currentApplication(User $user, Task $task): Response
    {
        return (
            $user->role === UserRole::Prestataire
        ) ? Response::allow() : Response::deny("Seul le prestataire propriétaire de cette candidature peut la voir.");
    }

    public function listMine(User $user): Response
    {
        return ($user->role === UserRole::Prestataire) ? Response::allow() : Response::deny("Seul le client propriétaire de ces candidatures peut les voir.");
    }

    public function accept(User $user, Task $task): Response
    {
        return (
            $user->id === $task->client_id
            && $user->role === UserRole::Client
        ) ? Response::allow() : Response::deny("Seul le client propriétaire de cette mission peut accepter la candidature.");
    }

    public function reject(User $user, Task $task): Response
    {
        return (
            $user->id === $task->client_id
            && $user->role === UserRole::Client
        ) ? Response::allow() : Response::deny("Seul le client propriétaire de cette mission peut rejeter la candidature.");
    }
}
