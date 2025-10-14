<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Agent;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgentFactory extends Factory
{
    public function definition(): array
    {
        return [];
    }

    public function fullAgent(): self
    {
        return $this->state(function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password1234'),
                'role' => UserRole::Agent,
                'is_active' => fake()->boolean(80),
            ]);

            $company = Company::create([
                'user_id' => $user->id,
                'name' => fake()->company(),
                'email' => fake()->unique()->companyEmail(),
                'is_active' => true,
            ]);

            return [
                'user_id' => $user->id,
                'company_id' => $company->id,
            ];
        });
    }
}
