<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => 'required|exists:companies,id',

            'day_of_week' => ['required', 'array', 'min:1'],
            'day_of_week.*' => [
                'required',
                Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun']),
            ],

            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',

            'am_time_in' => 'date_format:H:i',
            'am_time_out' => 'date_format:H:i|after:am_time_in',
            'am_require_photo_in' => 'boolean',
            'am_require_photo_out' => 'boolean',
            'am_require_location_in' => 'boolean',
            'am_require_location_out' => 'boolean',

            'pm_time_in' => 'nullable|date_format:H:i',
            'pm_time_out' => 'nullable|date_format:H:i|after:pm_time_in',
            'pm_require_photo_in' => 'boolean',
            'pm_require_photo_out' => 'boolean',
            'pm_require_location_in' => 'boolean',
            'pm_require_location_out' => 'boolean',

            'am_grace_period_minutes' => 'nullable|integer|min:0',
            'pm_grace_period_minutes' => 'nullable|integer|min:0',
            'allow_early_in' => 'boolean',
            'early_in_limit_minutes' => 'nullable|integer|min:0',
            'am_undertime_grace_minutes' => 'nullable|integer|min:0',
            'pm_undertime_grace_minutes' => 'nullable|integer|min:0',

            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:1|max:300',
        ];
    }
}
