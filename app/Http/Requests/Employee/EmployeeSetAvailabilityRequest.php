<?php

namespace App\Http\Requests\Employee;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeSetAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(Employee::MANUAL_AVAILABILITY_STATUSES)],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'status' => [
                'description' => 'Availability status. Allowed values: available, break, off. (in_session is set automatically via session start/end.)',
                'example'     => 'break',
            ],
        ];
    }
}
