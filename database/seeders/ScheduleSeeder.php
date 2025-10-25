<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();

        $count = (int) round($companies->count() * 0.9);

        $companies->random($count)->each(function ($company) {
            Schedule::factory()->create([
                'company_id' => $company->id,
            ]);
        });
    }
}
