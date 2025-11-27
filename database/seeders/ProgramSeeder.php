<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            ['code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology', 'required_hours' => 400],
            ['code' => 'BSCS', 'name' => 'Bachelor of Science in Computer Science', 'required_hours' => 400],
            ['code' => 'BSBA', 'name' => 'Bachelor of Science in Business Administration', 'required_hours' => 350],
            ['code' => 'BSA',  'name' => 'Bachelor of Science in Accountancy', 'required_hours' => 400],
            ['code' => 'BSED', 'name' => 'Bachelor of Secondary Education', 'required_hours' => 360],
            ['code' => 'BSAT', 'name' => 'Bachelor of Science in Accounting Technology', 'required_hours' => 380],
        ];

        foreach ($programs as $program) {
            Program::updateOrCreate(
                ['code' => $program['code']],
                [
                    'name' => $program['name'],
                    'description' => $program['name'] . ' program offered by the university.',
                    'required_hours' => $program['required_hours'],
                ]
            );
        }
    }
}
