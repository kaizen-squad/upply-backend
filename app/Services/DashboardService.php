<?php

namespace App\Services;

use App\Enums\TaskStatus;
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

        return [
            "tasks" => TaskResource::collection($tasks),
            "total_spent" => $totalSpent
        ];
    }

    public function forPrestataire(User $prestaire){
        Gate::authorize("prestataire-access-dashboard");

        $applications = Application::query()
        ->where('prestataire_id', $prestaire->id)
        ->with('task')
        ->get();

        $totalEarned = Transaction::query()->where('prestataire_id', $prestaire->id)
                        ->whereHas('task', fn($q) => $q->where('status', TaskStatus::VALIDATED))
                        ->where('status', 'released')
                        ->sum('amount_net');

        return [
            'applications' => $applications,
            'totalEarned' => $totalEarned
        ];
    }
}