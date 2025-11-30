<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class StudentListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $minutesAttended = $this->attendances ? $this->attendances->sum('duration_minutes') : 0;
        $hoursAttended = $minutesAttended / 60;

        $ojtStart = $this->ojt_start_date ? Carbon::parse($this->ojt_start_date) : null;
        $today = Carbon::today();

        $autoAbsenceHours = 0;
        $autoAbsencesCount = 0;

        if ($ojtStart && $this->company && $this->company->schedule) {
            $period = CarbonPeriod::create($ojtStart, $today);

            $scheduleDaysRaw = json_decode($this->company->schedule->day_of_week, true) ?? [];
            $scheduleDays = collect($scheduleDaysRaw)
                ->map(fn($day) => match ($day) {
                    'mon' => 1,
                    'tue' => 2,
                    'wed' => 3,
                    'thu' => 4,
                    'fri' => 5,
                    'sat' => 6,
                    'sun' => 7,
                    default => null
                })->filter()->toArray();

            Log::info('Raw schedule day_of_week for student ID ' . $this->student_id, [
                'day_of_week' => $this->company->schedule->day_of_week
            ]);
            Log::info("Scheduled days (numeric): ", $scheduleDays);

            $attendedDates = $this->attendances->pluck('date')->toArray();
            Log::info("Attended dates for student ID " . $this->student_id, $attendedDates);

            $absencePenalty = $this->program->absence_equivalent_hours ?? 0;

            foreach ($period as $date) {
                $dayOfWeek = $date->dayOfWeekIso;
                $dateStr = $date->format('Y-m-d');

                Log::info("Processing date $dateStr for student ID {$this->student_id} (dayOfWeekIso = $dayOfWeek)");

                if (!in_array($dayOfWeek, $scheduleDays)) {
                    Log::info("Skipping $dateStr for student ID {$this->student_id} (not a schedule day, dayOfWeekIso = $dayOfWeek)");
                    continue;
                }

                if (!in_array($dateStr, $attendedDates)) {
                    Log::info("Date $dateStr is absent for student ID {$this->student_id}");
                    $autoAbsenceHours += $absencePenalty;
                    $autoAbsencesCount++;
                    $absentDays[] = $dateStr;
                    Log::info("Auto absence recorded for student ID: {$this->student_id} on $dateStr, penalty hours: $absencePenalty");
                } else {
                    Log::info("Date $dateStr attended for student ID {$this->student_id}");
                }
            }
        }

        $manualAbsenceDays = $this->attendances
            ->filter(function ($att) {
                return $att->am_status === 'absent' || $att->pm_status === 'absent';
            })
            ->count();

        $manualAbsenceHours = $manualAbsenceDays * ($this->program->absence_equivalent_hours ?? 0);

        $effectiveRequired =
            $this->required_hours +
            $manualAbsenceHours +
            $autoAbsenceHours;

        $completion = $effectiveRequired > 0
            ? ($hoursAttended / $effectiveRequired) * 100
            : 0;

        $ojtEnd = $this->ojt_end_date;
        $projectedEnd = null;

        if ($ojtStart && !$ojtEnd) {
            $remainingHours = max(0, $effectiveRequired - $hoursAttended);
            $daysNeeded = ceil($remainingHours / 8);
            $projectedEnd = now()->addDays($daysNeeded)->format('Y-m-d');
        }

        $manualAbsenceDays = $this->attendances
            ->filter(function ($att) {
                return $att->am_status === 'absent' || $att->pm_status === 'absent';
            })
            ->count();

        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'status' => $this->status,

            'user' => $this->user,
            'program' => $this->program,
            'section' => $this->section,
            'advisor' => $this->advisor,
            'company' => $this->company,

            'evaluation_answers_count' => $this->evaluationAnswers()->count(),

            'hours_attended' => round($hoursAttended, 2),
            'absence_hours_manual' => $manualAbsenceHours,
            'absence_hours_auto' => $autoAbsenceHours,
            'auto_absences_count' => $autoAbsencesCount,
            'total_absences_count' => $manualAbsenceDays + $autoAbsencesCount,
            'effective_required_hours' => $effectiveRequired,
            'required_hours' => $this->required_hours,
            'completion' => round($completion, 2),

            'ojt_start_date' => $ojtStart,
            'ojt_end_date' => $ojtEnd,
            'projected_end_date' => $projectedEnd,
        ];
    }
}
