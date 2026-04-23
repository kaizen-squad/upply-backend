<?php

namespace Database\Seeders;

use App\Enums\TaskStatus;
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
        User::factory()
            ->count(10)
            ->prestataire()
            ->create();

        User::factory()
            ->count(5)
            ->client()
            ->has(Task::factory()
                    ->count(20)
                    ->state(new Sequence(
                        ['status' => TaskStatus::OPENED],
                        ['status' => TaskStatus::PENDING],
                    ))
                    )
            ->create();
    }
}
