<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
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
        return $this->allowsAccess($user, $task);
    }

    public function viewAny(User $user, Task $task): Response
    {
        return $this->allowsAccess($user, $task);
    }

    private function allowsAccess(User $user, Task $task): Response
    {
        $isOwner = ($user->role === UserRole::Client
            && $task->client_id === $user->id);

        if ($isOwner) {
            return Response::allow();
        }

        $isPrestataireForTask = ($user->role === UserRole::Prestataire
            && Application::query()
                ->where('task_id', $task->id)
                ->where('prestataire_id', $user->id)
                ->where('status', ApplicationStatus::ACCEPTED)
                ->exists()
        );

        if ($isPrestataireForTask) {
            return Response::allow();
        }

        return Response::deny('Seul le client propriétaire ou le prestataire retenu de cette tâche peuvent consulter les avis sur le livrable validé.');
    }
}
