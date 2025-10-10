<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'am_status' => 'nullable|in:present,absent,late,excused',
            'pm_status' => 'nullable|in:present,absent,late,excused',
            'remarks' => 'nullable|string',
        ];
    }
}
