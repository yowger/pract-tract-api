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

                        $isAbsent = fake()->boolean(10);

                        if ($isAbsent) {
                            $amIn = $amOut = $pmIn = $pmOut = null;
                        } else {

                            if (fake()->boolean(90)) {
                                $amIn = Carbon::parse($schedule->am_time_in)
                                    ->addMinutes(fake()->numberBetween(-5, 15))
                                    ->format('H:i:s');

                                $amOut = Carbon::parse($schedule->am_time_out)
                                    ->subMinutes(fake()->numberBetween(0, 15))
                                    ->format('H:i:s');
                            } else {
                                $amIn = $amOut = null;
                            }

                            if (fake()->boolean(90)) {
                                $pmIn = Carbon::parse($schedule->pm_time_in)
                                    ->addMinutes(fake()->numberBetween(-5, 15))
                                    ->format('H:i:s');

                                $pmOut = Carbon::parse($schedule->pm_time_out)
                                    ->subMinutes(fake()->numberBetween(0, 15))
                                    ->format('H:i:s');
                            } else {
                                $pmIn = $pmOut = null;
                            }
                        }


                        $duration = $this->calculateDuration($amIn, $amOut, $pmIn, $pmOut);

                        $amStatus = $this->determineStatus(
                            $amIn,
                            $amOut,
                            $schedule->am_time_in,
                            $schedule->am_time_out,
                            $schedule->am_grace_period_minutes,
                            $schedule->am_undertime_grace_minutes
                        );

                        $pmStatus = $this->determineStatus(
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

    private function calculateDuration($amIn, $amOut, $pmIn, $pmOut): int
    {
        $total = 0;

        if ($amIn && $amOut) {
            $total += Carbon::parse($amOut)->diffInMinutes(Carbon::parse($amIn));
        }

        if ($pmIn && $pmOut) {
            $total += Carbon::parse($pmOut)->diffInMinutes(Carbon::parse($pmIn));
        }

        return $total;
    }

    private function determineStatus($in, $out, $scheduledIn, $scheduledOut, $graceIn = 0, $graceOut = 0): ?string
    {
        if (!$in && !$out) {
            return 'absent';
        }

        $scheduledIn = Carbon::parse($scheduledIn);
        $scheduledOut = Carbon::parse($scheduledOut);

        $in = $in ? Carbon::parse($in) : null;
        $out = $out ? Carbon::parse($out) : null;

        $status = 'present';

        if ($in && $in->greaterThan($scheduledIn->copy()->addMinutes($graceIn))) {
            $status = 'late';
        }

        if ($out && $out->lessThan($scheduledOut->copy()->subMinutes($graceOut))) {
            $status = 'undertime';
        }

        return $status;
    }
}
