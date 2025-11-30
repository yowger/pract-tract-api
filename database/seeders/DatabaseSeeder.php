<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            ProgramSeeder::class,
            SectionSeeder::class,
            DirectorSeeder::class,
            // AgentSeeder::class,
            // AdvisorSeeder::class,
            // StudentSeeder::class,
            // ScheduleSeeder::class,
            // AttendanceSeeder::class,
        ]);
    }
}
