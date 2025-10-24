<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateAttendance extends Command
{
    protected $signature = 'app:generate-attendance';

    protected $description = 'Generate daily attendance records for all students based on schedule';

    public function handle()
    {
        $schedules = Schedule::with(['company.students'])
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->get();

        $count = 0;

        foreach ($schedules as $schedule) {
            $startDate = Carbon::parse($schedule->start_date);
            $endDate = Carbon::parse($schedule->end_date);
            $daysOfWeek = collect($schedule->day_of_week);

            $students = $schedule->company->students ?? collect();

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                if (!$daysOfWeek->contains(strtolower($date->format('l')))) {
                    continue;
                }

                foreach ($students as $student) {
                    $exists = Attendance::where('student_id', $student->id)
                        ->whereDate('date', $date->toDateString())
                        ->exists();

                    if (!$exists) {
                        Attendance::create([
                            'student_id' => $student->id,
                            'date' => $date->toDateString(),
                            'am_status' => null,
                            'pm_status' => null,
                        ]);

                        $count++;
                    }
                }
            }
        }

        $this->info("✅ Generated {$count} attendance records for all schedule days.");
    }
}
