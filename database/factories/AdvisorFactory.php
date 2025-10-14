<?php

namespace Database\Factories;

use App\Models\Advisor;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\UserRole;

class AdvisorFactory extends Factory
{
    protected $model = Advisor::class;

    public function definition(): array
    {
        return [];
    }

    public function configure()
    {
        return $this->afterCreating(function (Advisor $advisor) {
            $advisor->user()->create([
                'name' => $this->faker->name(),
                'email' => $this->faker->unique()->safeEmail(),
                'password' => bcrypt('password1234'),
                'role' => UserRole::Advisor,
                'is_active' => $this->faker->boolean(80),
            ]);
        });
    }

    public function fullAdvisor(): self
    {
        return $this->state(function () {
            $user = \App\Models\User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password1234'),
                'role' => UserRole::Advisor,
                'is_active' => fake()->boolean(80),
            ]);

            return [
                'user_id' => $user->id,
            ];
        });
    }
}
