<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Task;
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

    public function submit(User $user, Task $task): bool
    {
        if($user->role !== UserRole::Prestataire) return false;

        $hasAcceptedApplication = Application::where('task_id', $task->id)
            ->where('prestataire_id', $user->id)
            ->where('status', ApplicationStatus::ACCEPTED)
            ->exists();

        if(!$hasAcceptedApplication) return false;

        return true;
    }

    public function get(User $user, Task $task): bool
    {
        if($user->role !== UserRole::Client && $user->role !== UserRole::Prestataire) return false;

        $hasAcceptedApplication = Application::where('task_id', $task->id)
            ->where('prestataire_id', $user->id)
            ->where('status', ApplicationStatus::ACCEPTED)
            ->exists();

        if($hasAcceptedApplication) return true;

        $isTaskOwner = ($task->client_id === $user->id);

        if($isTaskOwner) return true;

        return false;
    }
}
