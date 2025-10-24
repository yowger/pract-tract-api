<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    public function record(Attendance $attendance, Schedule $schedule, Carbon $time): string
    {
        $session = $this->determineSession($schedule, $time);

        if ($session === 'am') {
            if (!$attendance->am_time_in) {
                $this->handleAmTimeIn($attendance, $schedule, $time);
                $message = 'AM time in recorded successfully';
            } elseif (!$attendance->am_time_out) {
                $this->handleAmTimeOut($attendance, $schedule, $time);
                $message = 'AM time out recorded successfully';
            } else {
                return 'AM attendance already completed';
            }
        } else {
            if (!$attendance->pm_time_in) {
                $this->handlePmTimeIn($attendance, $schedule, $time);
                $message = 'PM time in recorded successfully';
            } elseif (!$attendance->pm_time_out) {
                $this->handlePmTimeOut($attendance, $schedule, $time);
                $message = 'PM time out recorded successfully';
            } else {
                return 'PM attendance already completed';
            }
        }

        $this->updateTotalDuration($attendance);

        $attendance->save();

        return $message;
    }

    protected function updateTotalDuration(Attendance $attendance): void
    {
        $totalMinutes = 0;

        if ($attendance->am_time_in && $attendance->am_time_out) {
            $amIn = Carbon::parse($attendance->am_time_in);
            $amOut = Carbon::parse($attendance->am_time_out);
            $totalMinutes += $amOut->diffInMinutes($amIn);
        }

        if ($attendance->pm_time_in && $attendance->pm_time_out) {
            $pmIn = Carbon::parse($attendance->pm_time_in);
            $pmOut = Carbon::parse($attendance->pm_time_out);
            $totalMinutes += $pmOut->diffInMinutes($pmIn);
        }

        $attendance->duration_minutes = $totalMinutes > 0 ? $totalMinutes : null;
    }

    public function determineSession(Schedule $schedule, Carbon $time): string
    {
        $amStart = $schedule->am_time_in ? Carbon::parse($schedule->am_time_in) : null;
        $amEnd   = $schedule->am_time_out ? Carbon::parse($schedule->am_time_out) : null;
        $pmStart = $schedule->pm_time_in ? Carbon::parse($schedule->pm_time_in) : null;
        $pmEnd   = $schedule->pm_time_out ? Carbon::parse($schedule->pm_time_out) : null;

        Log::info('Determining session...', [
            'current_time' => $time->format('H:i:s'),
            'am_start' => $amStart?->format('H:i:s'),
            'am_end' => $amEnd?->format('H:i:s'),
            'pm_start' => $pmStart?->format('H:i:s'),
            'pm_end' => $pmEnd?->format('H:i:s'),
        ]);

        if ($amStart && $amEnd && $time->between($amStart, $amEnd)) {
            Log::info('Session determined as AM');
            return 'am';
        }

        if ($pmStart && $pmEnd && $time->between($pmStart, $pmEnd)) {
            Log::info('Session determined as PM');
            return 'pm';
        }

        $fallback = $time->lt(Carbon::parse('12:00')) ? 'am' : 'pm';
        Log::warning('Session determined by fallback rule', ['fallback' => $fallback]);

        return $fallback;
    }


    public function handleAmTimeIn(Attendance $attendance, Schedule $schedule, Carbon $time)
    {
        $scheduled = Carbon::parse($schedule->am_time_in);
        $grace = $schedule->am_grace_period_minutes ?? 0;

        if ($time->gt($scheduled->copy()->addMinutes($grace))) {
            $attendance->am_status = 'late';
        } else {
            $attendance->am_status = 'present';
        }

        if ($schedule->allow_early_in && $time->lt($scheduled)) {
            $earlyLimit = $scheduled->copy()->subMinutes($schedule->early_in_limit_minutes ?? 0);
            if ($time->lt($earlyLimit)) {
                $attendance->am_status = 'early';
            }
        }

        $attendance->am_time_in = $time;
    }

    public function handleAmTimeOut(Attendance $attendance, Schedule $schedule, Carbon $time)
    {
        $scheduled = Carbon::parse($schedule->am_time_out);
        $undertimeGrace = $schedule->am_undertime_grace_minutes ?? 0;

        if ($time->lt($scheduled->copy()->subMinutes($undertimeGrace))) {
            $attendance->am_status = 'undertime';
        }

        $attendance->am_time_out = $time;
    }

    public function handlePmTimeIn(Attendance $attendance, Schedule $schedule, Carbon $time)
    {
        $scheduled = Carbon::parse($schedule->pm_time_in);
        $grace = $schedule->pm_grace_period_minutes ?? 0;

        if ($time->gt($scheduled->copy()->addMinutes($grace))) {
            $attendance->pm_status = 'late';
        } else {
            $attendance->pm_status = 'present';
        }

        if ($schedule->allow_early_in && $time->lt($scheduled)) {
            $earlyLimit = $scheduled->copy()->subMinutes($schedule->early_in_limit_minutes ?? 0);
            if ($time->lt($earlyLimit)) {
                $attendance->pm_status = 'early';
            }
        }

        $attendance->pm_time_in = $time;
    }

    public function handlePmTimeOut(Attendance $attendance, Schedule $schedule, Carbon $time)
    {
        $scheduled = Carbon::parse($schedule->pm_time_out);
        $undertimeGrace = $schedule->pm_undertime_grace_minutes ?? 0;

        if ($time->lt($scheduled->copy()->subMinutes($undertimeGrace))) {
            $attendance->pm_status = 'undertime';
        }

        $attendance->pm_time_out = $time;
    }
}
