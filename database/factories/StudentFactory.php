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
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password1234'),
            'role' => 'student',
            'status' => $userStatus,
            'is_active' => $userStatus === UserStatus::Accepted,
        ]);

        $program = Program::inRandomOrder()->first();

        return [
            'user_id' => $user->id,
            'student_id' => $this->faker->unique()->numerify('S#####'),
            'program_id' => $program->id,
            'section_id' => Section::inRandomOrder()->first()->id,
            'advisor_id' => $userStatus === UserStatus::Accepted ? Advisor::inRandomOrder()->first()->id : null,
            'company_id' => $userStatus === UserStatus::Accepted ? Company::inRandomOrder()->first()->id : null,
            'required_hours' => $program->required_hours ?? 0,
        ];
    }
}
