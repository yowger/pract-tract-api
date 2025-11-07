<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_active' => $this->is_active,
            'student' => $this->whenLoaded('student', function () {
                return [
                    'id' => $this->student->id,
                    'student_id' => $this->student->student_id,
                    'program' => $this->student->program,
                    'section' => $this->student->section,
                    'advisor' => $this->student->advisor ?? null,
                    'company' => $this->student->company ?? null,
                    'schedule' => $this->student->company->schedule ?? null
                ];
            }),
        ];
    }
}
