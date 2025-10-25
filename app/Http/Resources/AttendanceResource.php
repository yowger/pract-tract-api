<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'student' => $this->student,
            'am_status' => $this->am_status,
            'am_time_in' => $this->am_time_in,
            'am_time_out' => $this->am_time_out,
            'am_photo_in' => $this->am_photo_in,
            'am_photo_out' => $this->am_photo_out,
            'am_lat_in' => $this->am_lat_in,
            'am_lng_in' => $this->am_lng_in,
            'am_lat_out' => $this->am_lat_out,
            'am_lng_out' => $this->am_lng_out,
            'pm_status' => $this->pm_status,
            'pm_time_in' => $this->pm_time_in,
            'pm_time_out' => $this->pm_time_out,
            'pm_photo_in' => $this->pm_photo_in,
            'pm_photo_out' => $this->pm_photo_out,
            'pm_lat_in' => $this->pm_lat_in,
            'pm_lng_in' => $this->pm_lng_in,
            'pm_lat_out' => $this->pm_lat_out,
            'pm_lng_out' => $this->pm_lng_out,
            'duration_minutes' => $this->duration_minutes,
            'remarks' => $this->remarks,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
