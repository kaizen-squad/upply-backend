<?php

namespace Database\Factories;

use App\Models\Model;
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
            "title" => fake()->jobTitle(),
            "description" => fake()->paragraph(),
            "budget" => fake()->numberBetween(10000, 100000),
            "deadline" => fake()->dateTimeBetween('now', '+3 months'),
        ];
    }
}
