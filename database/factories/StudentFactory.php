<?php

namespace Database\Factories;

use App\Enums\UserStatus;
use App\Models\Advisor;
use App\Models\Company;
use App\Models\Program;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $statuses = array_merge(
            array_fill(0, 70, UserStatus::Accepted),
            array_fill(0, 20, UserStatus::Pending),
            array_fill(0, 10, UserStatus::Rejected),
        );

        $userStatus = $this->faker->randomElement($statuses);

        $user = User::create([
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password1234'),
            'role' => 'student',
            'status' => $userStatus,
            'is_active' => $userStatus === UserStatus::Accepted,
        ]);

        return [
            'user_id'    => $user->id, // ✅ Required!
            'student_id' => $this->faker->unique()->numerify('S#####'),
            'program_id' => Program::inRandomOrder()->first()->id,
            'section_id' => Section::inRandomOrder()->first()->id,
            'advisor_id' => null,
            'company_id' => null,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Student $student) {
            if ($student->user->status === UserStatus::Accepted) {
                $student->update([
                    'advisor_id' => Advisor::inRandomOrder()->first()->id,
                    'company_id' => Company::inRandomOrder()->first()->id,
                ]);
            }
        });
    }
}
