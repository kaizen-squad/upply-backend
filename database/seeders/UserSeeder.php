<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // This command will create 5 "prestataire".
        User::factory()
            ->count(16)
            ->prestataire()
            ->create();

        User::factory()
            ->count(5)
            ->client()
            ->has(Task::factory()->count(2)->create())
            ->create();
    }
}
