<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            ['code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology'],
            ['code' => 'BSCS', 'name' => 'Bachelor of Science in Computer Science'],
            ['code' => 'BSBA', 'name' => 'Bachelor of Science in Business Administration'],
            ['code' => 'BSA',  'name' => 'Bachelor of Science in Accountancy'],
            ['code' => 'BSEd', 'name' => 'Bachelor of Secondary Education'],
            ['code' => 'BSAT', 'name' => 'Bachelor of Science in Accounting Technology'],
        ];

        foreach ($programs as $program) {
            Program::updateOrCreate(
                ['code' => $program['code']],
                [
                    'name' => $program['name'],
                    'description' => $program['name'] . ' program offered by the university.',
                ]
            );
        }
    }
}
