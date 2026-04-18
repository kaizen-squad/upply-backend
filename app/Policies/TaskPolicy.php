<?php

namespace App\Policies;

use App\Enums\UserRole;
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

    public function create(User $user){
        return $user->role == UserRole::CLIENT;
    }
}
