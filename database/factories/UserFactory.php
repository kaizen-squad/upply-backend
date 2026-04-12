<?php

namespace Database\Factories;

<<<<<<< HEAD
use App\Enums\UserRole;
=======
>>>>>>> 17ff392 (feat(architecture): Set up the backend code base structure)
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
<<<<<<< HEAD
            'id' => fake()->uuid(),
=======
>>>>>>> 17ff392 (feat(architecture): Set up the backend code base structure)
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
<<<<<<< HEAD
            'phone' => fake()->phoneNumber(),
            'rating_avg' => fake()->randomFloat(2, 1, 5),
=======
>>>>>>> 17ff392 (feat(architecture): Set up the backend code base structure)
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
<<<<<<< HEAD

    public function client(): static
    {
        return $this->state(fn (array $attributes) =>[
            'role' => UserRole::CLIENT,
        ]);
    }

    public function prestataire(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::PRESTATAIRE,
        ]);
    }
=======
>>>>>>> 17ff392 (feat(architecture): Set up the backend code base structure)
}
