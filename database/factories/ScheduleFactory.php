<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    public function definition(): array
    {
        $daysOfWeek = collect(['mon', 'tue', 'wed', 'thu', 'fri'])
            ->values()
            ->toArray();

        return [
            'company_id' => Company::inRandomOrder()->value('id'),

            'day_of_week' => json_encode($daysOfWeek),

            'start_date' => now()->subMonths(3)->toDateString(),
            'end_date' => now()->toDateString(),

            'am_time_in' => '08:00:00',
            'am_time_out' => '12:00:00',
            'am_require_photo_in' => fake()->boolean(30),
            'am_require_photo_out' => fake()->boolean(30),
            'am_require_location_in' => fake()->boolean(50),
            'am_require_location_out' => fake()->boolean(50),

            'pm_time_in' => '13:00:00',
            'pm_time_out' => '17:00:00',
            'pm_require_photo_in' => fake()->boolean(30),
            'pm_require_photo_out' => fake()->boolean(30),
            'pm_require_location_in' => fake()->boolean(50),
            'pm_require_location_out' => fake()->boolean(50),

            'am_grace_period_minutes' => 10,
            'pm_grace_period_minutes' => 10,
            'allow_early_in' => fake()->boolean(),
            'early_in_limit_minutes' => fake()->randomElement([0, 10, 15]),
            'am_undertime_grace_minutes' => 5,
            'pm_undertime_grace_minutes' => 5,
        ];
    }
}
