<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'status' => $this->status,
            'user' => $this->user,
            'program' => $this->program,
            'section' => $this->section,
            'advisor' => $this->advisor,
            'company' => $this->company,
        ];
    }
}
