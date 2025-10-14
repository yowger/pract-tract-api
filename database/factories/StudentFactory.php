<?php

namespace Database\Factories;

use App\Enums\StudentStatus;
use App\Models\Advisor;
use App\Models\Company;
use App\Models\Program;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => $this->faker->unique()->numerify('S#####'),
            'program_id' => Program::inRandomOrder()->first()->id,
            'section_id' => Section::inRandomOrder()->first()->id,
            'advisor_id' => Advisor::inRandomOrder()->first()->id,
            'company_id' => Company::inRandomOrder()->first()->id,
            'status' => StudentStatus::Pending,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Student $student) {
            $student->user()->create([
                'name' => $this->faker->name(),
                'email' => $this->faker->unique()->safeEmail(),
                'password' => bcrypt('password1234'),
                'role' => 'student',
                'is_active' => fake()->boolean(80),
            ]);
        });
    }

    public function fullStudent(): self
    {
        return $this->state(function () {
            $user = \App\Models\User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password1234'),
                'role' => 'student',
                'is_active' => fake()->boolean(80),
            ]);

            return [
                'user_id' => $user->id,
            ];
        });
    }
}
