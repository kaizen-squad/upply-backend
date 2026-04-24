<?php

namespace Database\Seeders;

use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // This command will create 5 "client" and 10 "prestataire".
        User::factory(10)->create([
            'role' => UserRole::Prestataire,
            'phone' => '22990' . rand(100000, 999999),
        ]);

        User::factory()
            ->count(5)
            ->client()
            ->has(Task::factory()
                ->count(5)  // Reduced for testing efficiency
                ->state(new Sequence(
                    ['status' => TaskStatus::OPENED],
                    ['status' => TaskStatus::PENDING],
                ))
                ->afterCreating(function ($task) {
                    // For each task, create one accepted application from a prestataire
                    $prestataire = \App\Models\User::where('role', UserRole::Prestataire)->inRandomOrder()->first();
                    if ($prestataire) {
                        \App\Models\Application::create([
                            'task_id' => $task->id,
                            'prestataire_id' => $prestataire->id,
                            'message' => 'Je suis intéressé par cette tâche.',
                            'status' => 'ACCEPTEE',
                        ]);
                    }
                }))
            ->create();
    }
}
