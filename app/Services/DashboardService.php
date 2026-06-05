<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\TaskStatus;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\TaskResource;
use App\Models\Application;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class DashboardService{
    public function forClient(User $client){
        Gate::authorize("client-access-dashboard");

        $tasks = Task::query()
        ->where('client_id', $client->id)
        ->withCount('applications')
        ->get();

        $totalSpent = Transaction::query()->whereHas('task', fn($q) => $q
                                            ->where('client_id', $client->id)
                                            ->where('status', TaskStatus::VALIDATED))
        ->where('status', 'released')
        ->sum('amount_gross');

        $opened_tasks = Task::query()
        ->where('client_id', $client->id)
        ->where('status', TaskStatus::OPENED)
        ->count();

        $pending_tasks = Task::query()
        ->where('client_id', $client->id)
        ->where('status', TaskStatus::PENDING)
        ->count();

        $validated_tasks = Task::query()
        ->where('client_id', $client->id)
        ->where('status', TaskStatus::VALIDATED)
        ->count();

        return [
            "tasks" => TaskResource::collection($tasks),
            "statistics" => [
                "opened" => $opened_tasks,
                "pending" => $pending_tasks,
                "validated" => $validated_tasks
            ],
            "total_spent" => $totalSpent
        ];
    }

    public function forPrestataire(User $prestataire){
        Gate::authorize("prestataire-access-dashboard");

        $applications = Application::query()
        ->where('prestataire_id', $prestataire->id)
        ->with('task')
        ->get();

        $totalEarned = Transaction::query()->where('prestataire_id', $prestataire->id)
                        ->whereHas('task', fn($q) => $q->where('status', TaskStatus::VALIDATED))
                        ->where('status', 'released')
                        ->sum('amount_net');

        $tasks = Task::query()
        ->whereHas('applications', function ($query) use ($prestataire){
            $query->where('prestataire_id', $prestataire->id);
        })
        ->get();

        $waiting_applications = Application::query()
        ->where('prestataire_id', $prestataire->id)
        ->where('status', ApplicationStatus::PENDING)
        ->count();

        $active_missions = Task::query()
        ->whereIn('status', [TaskStatus::PENDING, TaskStatus::DELIVERED])
        ->whereHas('applications', function($query) use ($prestataire){
            $query->where('prestataire_id', $prestataire->id);
        })
        ->count();


        return [
            'tasks' => TaskResource::collection($tasks),
            'applications' => ApplicationResource::collection($applications),
            'statistics' => [
                'waiting_applications' => $waiting_applications,
                'active_missions' => $active_missions
            ]
        ];
    }
}