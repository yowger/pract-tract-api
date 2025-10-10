<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'am_status' => 'nullable|in:present,absent,late,excused',
            'pm_status' => 'nullable|in:present,absent,late,excused',
            'remarks' => 'nullable|string',
        ];
    }
}
