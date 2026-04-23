<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class DeliverablePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function submit(User $user): bool
    {
        return $user->status === UserRole::Prestataire;
    }
}
