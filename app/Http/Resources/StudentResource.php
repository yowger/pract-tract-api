<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
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
            'user' => array_merge(
                $this->user->only([
                    'id',
                    'name',
                    'email',
                    'role',
                    'phone',
                    'address',
                ]),
                [
                    'created_at' => $this->user->created_at->toDateTimeString(),
                    'updated_at' => $this->user->updated_at->toDateTimeString(),
                ]
            ),
            'program' => $this->program,
            'section' => $this->section,
            'advisor' => $this->advisor,
            'company' => $this->company,
        ];
    }
}
