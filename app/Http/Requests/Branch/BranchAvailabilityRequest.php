<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;

class BranchAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_uuid' => ['nullable', 'string', 'exists:employees,uuid'],
        ];
    }

    public function queryParameters(): array
    {
        return [
            'employee_uuid' => ['description' => 'Filter by a specific employee UUID.', 'example' => '01JABCDEF1234567890ABCDEFGH'],
        ];
    }
}
