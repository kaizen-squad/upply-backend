<?php

namespace Database\Factories;

<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
use App\Enums\UserRole;
=======
>>>>>>> 17ff392 (feat(architecture): Set up the backend code base structure)
=======
use App\Enums\UserRole;
>>>>>>> f5b2ff4 (fix(migrations): Added id column name - Removed min constraint on review rating column)
=======
use App\Enums\UserRole;
>>>>>>> f5b2ff4 (fix(migrations): Added id column name - Removed min constraint on review rating column)
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
<<<<<<< HEAD
<<<<<<< HEAD
            'id' => fake()->uuid(),
=======
>>>>>>> 17ff392 (feat(architecture): Set up the backend code base structure)
=======
            'id' => fake()->uuid(),
>>>>>>> f5b2ff4 (fix(migrations): Added id column name - Removed min constraint on review rating column)
=======
            'id' => fake()->uuid(),
>>>>>>> f5b2ff4 (fix(migrations): Added id column name - Removed min constraint on review rating column)
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
            'phone' => fake()->phoneNumber(),
            'rating_avg' => fake()->randomFloat(2, 1, 5),
=======
>>>>>>> 17ff392 (feat(architecture): Set up the backend code base structure)
=======
            'phone' => fake()->phoneNumber(),
            'rating_avg' => fake()->randomFloat(2, 1, 5),
>>>>>>> f5b2ff4 (fix(migrations): Added id column name - Removed min constraint on review rating column)
=======
            'phone' => fake()->phoneNumber(),
            'rating_avg' => fake()->randomFloat(2, 1, 5),
>>>>>>> f5b2ff4 (fix(migrations): Added id column name - Removed min constraint on review rating column)
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
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> f5b2ff4 (fix(migrations): Added id column name - Removed min constraint on review rating column)
=======
>>>>>>> f5b2ff4 (fix(migrations): Added id column name - Removed min constraint on review rating column)

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
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 17ff392 (feat(architecture): Set up the backend code base structure)
=======
>>>>>>> f5b2ff4 (fix(migrations): Added id column name - Removed min constraint on review rating column)
=======
>>>>>>> f5b2ff4 (fix(migrations): Added id column name - Removed min constraint on review rating column)
}
