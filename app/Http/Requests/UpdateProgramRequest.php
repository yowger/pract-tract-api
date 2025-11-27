<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:programs,code,' . $this->program->id,
            'description' => 'nullable|string',
            'required_hours' => 'sometimes|integer|min:0',
        ];
    }
}
