<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Deliverable;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DeliverablePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function submit(User $user, Task $task): Response
    {
        if($user->role !== UserRole::Prestataire) return Response::deny("Vous devez être un prestataire pour éffectuer cette action.");

        $hasAcceptedApplication = Application::where('task_id', $task->id)
            ->where('prestataire_id', $user->id)
            ->where('status', ApplicationStatus::ACCEPTED)
            ->exists();

        if(!$hasAcceptedApplication) return Response::deny("Vous candidature doit être accepté au préalable.");

        return Response::allow();
    }

    public function get(User $user, Task $task): Response
    {
        if($user->role !== UserRole::Client && $user->role !== UserRole::Prestataire) return Response::deny("Vous devez être un client ou un prestataire pour effectuer cette action.");

        $hasAcceptedApplication = Application::where('task_id', $task->id)
            ->where('prestataire_id', $user->id)
            ->where('status', ApplicationStatus::ACCEPTED)
            ->exists();

        if($hasAcceptedApplication) return Response::allow();

        $isTaskOwner = ($task->client_id === $user->id);

        if($isTaskOwner) return Response::allow();

        return Response::deny("Vous ne pouvez pas voir ce livrable. Vous n'avez pas les permissions requises.");
    }

    public function validate(User $user, Deliverable $deliverable): Response
    {
        return (
            $user->id === $deliverable->task->client_id
            && $user->role === UserRole::Client
        ) ? Response::allow() : Response::deny("Seul le client propriétaire de cette mission peut valider le livrable.");
    }
}
