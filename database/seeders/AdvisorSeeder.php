<?php

namespace Database\Seeders;

use App\Models\Advisor;
use Illuminate\Database\Seeder;

class AdvisorSeeder extends Seeder
{
    public function run(): void
    {
        Advisor::factory()->count(10)->fullAdvisor()->create();
    }
}
