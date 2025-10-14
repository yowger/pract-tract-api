<?php

namespace Database\Factories;

use App\Models\Advisor;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\UserRole;
use App\Enums\UserStatus;

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
            $statuses = [
                UserStatus::Accepted->value => 70,
                UserStatus::Pending->value => 20,
                UserStatus::Rejected->value => 10,
            ];

            $status = $this->faker->randomElement(
                array_merge(
                    array_fill(0, $statuses[UserStatus::Accepted->value], UserStatus::Accepted),
                    array_fill(0, $statuses[UserStatus::Pending->value], UserStatus::Pending),
                    array_fill(0, $statuses[UserStatus::Rejected->value], UserStatus::Rejected)
                )
            );

            $advisor->user()->create([
                'name' => $this->faker->name(),
                'email' => $this->faker->unique()->safeEmail(),
                'password' => bcrypt('password1234'),
                'role' => UserRole::Advisor,
                'status' => $status,
                'is_active' => $status === UserStatus::Accepted,
            ]);
        });
    }

    public function fullAdvisor(): self
    {
        return $this->state(function () {
            $statuses = [
                UserStatus::Accepted->value => 70,
                UserStatus::Pending->value => 20,
                UserStatus::Rejected->value => 10,
            ];

            $status = $this->faker->randomElement(
                array_merge(
                    array_fill(0, $statuses[UserStatus::Accepted->value], UserStatus::Accepted),
                    array_fill(0, $statuses[UserStatus::Pending->value], UserStatus::Pending),
                    array_fill(0, $statuses[UserStatus::Rejected->value], UserStatus::Rejected)
                )
            );

            $user = \App\Models\User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password1234'),
                'role' => UserRole::Advisor,
                'status' => $status,
                'is_active' => $status === UserStatus::Accepted,
            ]);

            return [
                'user_id' => $user->id,
            ];
        });
    }
}
