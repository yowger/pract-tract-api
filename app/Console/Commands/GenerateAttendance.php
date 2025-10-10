<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Attendance;
use Carbon\Carbon;

class GenerateAttendance extends Command
{
    protected $signature = 'app:generate-attendance';

    protected $description = 'Generate daily attendance records for all students based on schedule';

    public function handle()
    {
        $today = Carbon::today();
        $dayOfWeek = strtolower($today->format('l'));

        $schedules = Schedule::where('day_of_week', $dayOfWeek)
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->get();

        $count = 0;

        foreach ($schedules as $schedule) {
            $students = $schedule->company->students ?? collect();

            foreach ($students as $student) {
                $exists = Attendance::where('student_id', $student->id)
                    ->whereDate('date', $today)
                    ->exists();

                if (!$exists) {
                    Attendance::create([
                        'student_id' => $student->id,
                        'date' => $today,
                        'am_status' => null,
                        'pm_status' => null,
                    ]);

                    $count++;
                }
            }
        }

        $this->info("✅ Generated {$count} attendance records for {$today->toDateString()}");
    }
}
