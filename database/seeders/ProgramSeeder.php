<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            [
                'code' => 'BSCRIM',
                'name' => 'Bachelor of Science in Criminology',
            ],
            [
                'code' => 'BSISM',
                'name' => 'Bachelor of Science in Industrial Security Management',
            ],
            [
                'code' => 'BSIT',
                'name' => 'Bachelor of Science in Information Technology',
            ],
            [
                'code' => 'BSA',
                'name' => 'Bachelor of Science in Agriculture',
            ],
            [
                'code' => 'BSED-MATH',
                'name' => 'Bachelor of Secondary Education Major in Mathematics',
            ],
            [
                'code' => 'BSED-FIL',
                'name' => 'Bachelor of Secondary Education Major in Filipino',
            ],
            [
                'code' => 'BSED-ENG',
                'name' => 'Bachelor of Secondary Education Major in English',
            ],
            [
                'code' => 'BPED',
                'name' => 'Bachelor of Physical Education',
            ],
            [
                'code' => 'BEED',
                'name' => 'Bachelor of Elementary Education',
            ],
            [
                'code' => 'BAELS',
                'name' => 'Bachelor of Arts in English Language Studies',
            ],
        ];

        foreach ($programs as $program) {
            Program::updateOrCreate(
                ['code' => $program['code']],
                [
                    'name' => $program['name'],
                    'description' => $program['name'] . ' program offered by the university.',
                    'required_hours' => 400,
                    'absence_equivalent_hours' => 8,
                ]
            );
        }
    }
}
