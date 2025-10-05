<?php

namespace Database\Factories;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
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
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'role' => UserRole::Student,
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'is_active' => true,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn() => [
            'role' => UserRole::Admin,
            'email' => env('ADMIN_EMAIL', 'admin@gmail.com'),
            'password' => bcrypt(env('ADMIN_PASSWORD', 'password123')),
        ]);
    }
}
