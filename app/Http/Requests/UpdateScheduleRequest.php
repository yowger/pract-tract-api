<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'day_of_week' => ['sometimes', Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])],
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'am_time_in' => 'nullable|date_format:H:i',
            'am_time_out' => 'nullable|date_format:H:i|after:am_time_in',
            'pm_time_in' => 'nullable|date_format:H:i',
            'pm_time_out' => 'nullable|date_format:H:i|after:pm_time_in',
            'am_require_photo_in' => 'boolean',
            'am_require_photo_out' => 'boolean',
            'am_require_location_in' => 'boolean',
            'am_require_location_out' => 'boolean',
            'pm_require_photo_in' => 'boolean',
            'pm_require_photo_out' => 'boolean',
            'pm_require_location_in' => 'boolean',
            'pm_require_location_out' => 'boolean',
        ];
    }
}
