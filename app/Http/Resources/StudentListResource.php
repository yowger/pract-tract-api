<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class StudentListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $minutesAttended = $this->attendances ? $this->attendances->sum('duration_minutes') : 0;

        Log::info("Student ID {$this->id} total minutes attended: {$minutesAttended}");


        $hoursAttended = $minutesAttended / 60;

        $completion = $this->required_hours > 0
            ? ($hoursAttended / $this->required_hours) * 100
            : 0;

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
            'required_hours' => $this->required_hours,
            'completion' => round($completion, 2),
        ];
    }
}
