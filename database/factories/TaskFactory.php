<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "id" => fake()->uuid(),
            "client_id" => User::factory(),
            "title" => fake()->jobTitle(),
            "description" => fake()->paragraph(),
            "budget" => fake()->numberBetween(10000, 100000),
            "deadline" => fake()->dateTimeBetween('now', '+3 months'),
            "status" => TaskStatus::OPENED,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::PENDING
        ]);
    }

    public function validated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::DELIVERED
        ]);
    }
}
