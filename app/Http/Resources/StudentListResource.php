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

        $excusedHours = $this->attendances
            ->where('status', 'excused')
            ->sum('absence_equivalent_hours');

        $ojtStart = $this->ojt_start_date ? Carbon::parse($this->ojt_start_date) : null;
        $today = Carbon::today();

        $autoAbsenceHours = 0;

        if ($ojtStart) {
            $period = CarbonPeriod::create($ojtStart, $today);

            $attendedDates = $this->attendances->pluck('date')->toArray();
            $absencePenalty = $this->program->absence_equivalent_hours ?? 0;

            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');

                if (!in_array($dateStr, $attendedDates)) {
                    $autoAbsenceHours += $absencePenalty;
                }
            }
        }

        $manualAbsenceHours = $this->attendances
            ->where('status', 'absent')
            ->sum('absence_equivalent_hours');

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
            'effective_required_hours' => $effectiveRequired,
            'required_hours' => $this->required_hours,
            'completion' => round($completion, 2),

            'ojt_start_date' => $ojtStart,
            'ojt_end_date' => $ojtEnd,
            'projected_end_date' => $projectedEnd,
        ];
    }
}
