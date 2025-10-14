<?php

namespace Database\Factories;

use App\Enums\StudentStatus;
use App\Enums\UserStatus;
use App\Models\Advisor;
use App\Models\Company;
use App\Models\Program;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        $studentStatus = $this->faker->randomElement([
            StudentStatus::Pending,
            StudentStatus::Active,
            StudentStatus::Completed,
        ]);
        return [
            'student_id' => $this->faker->unique()->numerify('S#####'),
            'program_id' => Program::inRandomOrder()->first()->id,
            'section_id' => Section::inRandomOrder()->first()->id,
            'advisor_id' => $studentStatus === StudentStatus::Active
                ? Advisor::inRandomOrder()->first()->id
                : null,
            'company_id' => $studentStatus === StudentStatus::Active
                ? Company::inRandomOrder()->first()->id
                : null,
            'status' => $studentStatus,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Student $student) {
            $statuses = array_merge(
                array_fill(0, 70, UserStatus::Accepted),
                array_fill(0, 20, UserStatus::Pending),
                array_fill(0, 10, UserStatus::Rejected),
            );

            $userStatus = $this->faker->randomElement($statuses);

            $student->user()->create([
                'name' => $this->faker->name(),
                'email' => $this->faker->unique()->safeEmail(),
                'password' => bcrypt('password1234'),
                'role' => 'student',
                'status' => $userStatus,
                'is_active' => $userStatus === UserStatus::Accepted,
            ]);
        });
    }

    public function fullStudent(): self
    {
        return $this->state(function () {
            $statuses = [
                UserStatus::Accepted->value => 70,
                UserStatus::Pending->value => 20,
                UserStatus::Rejected->value => 10,
            ];

            $status = $this->faker->randomElement(
                array_merge(
                    array_fill(0, $statuses[UserStatus::Accepted->value], UserStatus::Accepted->value),
                    array_fill(0, $statuses[UserStatus::Pending->value], UserStatus::Pending->value),
                    array_fill(0, $statuses[UserStatus::Rejected->value], UserStatus::Rejected->value),
                )
            );

            $user = \App\Models\User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password1234'),
                'role' => 'student',
                'status' => $status,
            ]);

            return [
                'user_id' => $user->id,
            ];
        });
    }
}
