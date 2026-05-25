<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReviewPolicy
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
            $user->role === UserRole::Client
            && $task->client_id === $user->id
        ) ? Response::allow() : Response::deny("Seul le client propriétaire de cette tâche peut soumettre un commentaire sur le livrable validé.");
    }
}
