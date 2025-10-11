<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Schedule;
use Carbon\Carbon;

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

        $attendance->save();

        return $message;
    }

    public function determineSession(Schedule $schedule, Carbon $time): string
    {
        $amStart = $schedule->am_time_in ? Carbon::parse($schedule->am_time_in) : null;
        $amEnd   = $schedule->am_time_out ? Carbon::parse($schedule->am_time_out) : null;
        $pmStart = $schedule->pm_time_in ? Carbon::parse($schedule->pm_time_in) : null;
        $pmEnd   = $schedule->pm_time_out ? Carbon::parse($schedule->pm_time_out) : null;

        if ($amStart && $amEnd && $time->between($amStart, $amEnd)) return 'am';
        if ($pmStart && $pmEnd && $time->between($pmStart, $pmEnd)) return 'pm';

        return $time->lt(Carbon::parse('12:00')) ? 'am' : 'pm';
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
