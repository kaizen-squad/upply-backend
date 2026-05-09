<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Task;
use App\Models\User;

class ReviewPolicy
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
            $user->role === UserRole::Client
            && $task->client_id === $user->id
        );
    }
}
