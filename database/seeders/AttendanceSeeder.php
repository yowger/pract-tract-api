<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = Schedule::with(['company.students'])->get();

        foreach ($schedules as $schedule) {
            if (!$schedule->company || $schedule->company->students->isEmpty()) {
                continue;
            }

            $start = Carbon::parse($schedule->start_date);
            $end = Carbon::parse($schedule->end_date);

            while ($start <= $end) {
                if ($start->isWeekday()) {

                    foreach ($schedule->company->students as $student) {

                        if (Attendance::where('student_id', $student->id)
                            ->whereDate('date', $start->format('Y-m-d'))
                            ->exists()
                        ) {
                            continue;
                        }

                        $isAbsent = fake()->boolean(12);

                        if ($isAbsent) {
                            $amIn = $amOut = $pmIn = $pmOut = null;
                        } else {

                            $scenario = fake()->randomElement([
                                'both_ok',
                                'am_late',
                                'pm_late',
                                'am_ut',
                                'pm_ut',
                                'rare_both',
                                'absent_am',
                                'absent_pm'
                            ]);

                            if ($scenario === 'absent_am') {
                                $amIn = $amOut = null;
                            } elseif ($scenario === 'am_late') {
                                $amIn = $start->copy()->setTimeFromTimeString($schedule->am_time_in)
                                    ->addMinutes(fake()->numberBetween($schedule->am_grace_period_minutes + 1, 30))
                                    ->format('H:i:s');

                                $amOut = $start->copy()->setTimeFromTimeString($schedule->am_time_out)
                                    ->subMinutes(0)
                                    ->format('H:i:s');
                            } else {
                                $amIn = $start->copy()->setTimeFromTimeString($schedule->am_time_in)
                                    ->addMinutes(fake()->numberBetween(-3, 10))
                                    ->format('H:i:s');

                                $amOut = $start->copy()->setTimeFromTimeString($schedule->am_time_out)
                                    ->subMinutes(
                                        match ($scenario) {
                                            'am_ut', 'rare_both' => fake()->numberBetween(1, 10),
                                            default => 0,
                                        }
                                    )->format('H:i:s');
                            }

                            if ($scenario === 'absent_pm') {
                                $pmIn = $pmOut = null;
                            } elseif ($scenario === 'pm_late') {
                                $pmIn = $start->copy()->setTimeFromTimeString($schedule->pm_time_in)
                                    ->addMinutes(fake()->numberBetween($schedule->pm_grace_period_minutes + 1, 30))
                                    ->format('H:i:s');

                                $pmOut = $start->copy()->setTimeFromTimeString($schedule->pm_time_out)
                                    ->subMinutes(0)
                                    ->format('H:i:s');
                            } else {
                                $pmIn = $start->copy()->setTimeFromTimeString($schedule->pm_time_in)
                                    ->addMinutes(fake()->numberBetween(-3, 10))
                                    ->format('H:i:s');

                                $pmOut = $start->copy()->setTimeFromTimeString($schedule->pm_time_out)
                                    ->subMinutes(
                                        match ($scenario) {
                                            'pm_ut', 'rare_both' => fake()->numberBetween(1, 10),
                                            default => 0,
                                        }
                                    )->format('H:i:s');
                            }
                        }

                        $attendanceDate = $start->format('Y-m-d');
                        $duration = $this->calculateDuration($attendanceDate, $amIn, $amOut, $pmIn, $pmOut);

                        $amStatus = $this->determineStatus(
                            $attendanceDate,
                            $amIn,
                            $amOut,
                            $schedule->am_time_in,
                            $schedule->am_time_out,
                            $schedule->am_grace_period_minutes,
                            $schedule->am_undertime_grace_minutes
                        );

                        $pmStatus = $this->determineStatus(
                            $attendanceDate,
                            $pmIn,
                            $pmOut,
                            $schedule->pm_time_in,
                            $schedule->pm_time_out,
                            $schedule->pm_grace_period_minutes,
                            $schedule->pm_undertime_grace_minutes
                        );

                        Attendance::create([
                            'student_id' => $student->id,
                            'date' => $start->format('Y-m-d'),

                            'am_status' => $amStatus,
                            'pm_status' => $pmStatus,

                            'am_time_in' => $amIn,
                            'am_time_out' => $amOut,
                            'pm_time_in' => $pmIn,
                            'pm_time_out' => $pmOut,

                            'am_lat_in' => $this->fakeLocation(),
                            'am_lng_in' => $this->fakeLocation(),
                            'am_lat_out' => $this->fakeLocation(),
                            'am_lng_out' => $this->fakeLocation(),

                            'pm_lat_in' => $this->fakeLocation(),
                            'pm_lng_in' => $this->fakeLocation(),
                            'pm_lat_out' => $this->fakeLocation(),
                            'pm_lng_out' => $this->fakeLocation(),

                            'duration_minutes' => $duration,
                        ]);
                    }
                }

                $start->addDay();
            }
        }
    }

    private function fakeLocation(): ?float
    {
        return fake()->boolean(80)
            ? fake()->latitude(14, 16)
            : null;
    }

    private function calculateDuration(string $date, $amIn, $amOut, $pmIn, $pmOut): int
    {
        $total = 0;

        $make = function ($time) use ($date) {
            if (!$time) return null;
            return Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time);
        };

        $amInDt = $make($amIn);
        $amOutDt = $make($amOut);
        $pmInDt = $make($pmIn);
        $pmOutDt = $make($pmOut);

        if ($amInDt && $amOutDt) {
            $total += $amInDt->diffInMinutes($amOutDt);
        }

        if ($pmInDt && $pmOutDt) {
            $total += $pmInDt->diffInMinutes($pmOutDt);
        }

        return $total;
    }

    private function determineStatus($date, $in, $out, $scheduledIn, $scheduledOut, $graceIn = 0, $graceOut = 0): ?string
    {
        if (!$in && !$out) {
            return 'absent';
        }

        $scheduledInDt = $scheduledIn ? Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . Carbon::parse($scheduledIn)->format('H:i:s')) : null;
        $scheduledOutDt = $scheduledOut ? Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . Carbon::parse($scheduledOut)->format('H:i:s')) : null;

        $inDt = $in ? Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . Carbon::parse($in)->format('H:i:s')) : null;
        $outDt = $out ? Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . Carbon::parse($out)->format('H:i:s')) : null;

        $status = 'present';

        if ($inDt && $scheduledInDt && $inDt->greaterThan($scheduledInDt->copy()->addMinutes($graceIn))) {
            $status = 'late';
        }

        if ($outDt && $scheduledOutDt && $outDt->lessThan($scheduledOutDt->copy()->subMinutes($graceOut))) {
            $status = 'undertime';
        }

        return $status;
    }
}
