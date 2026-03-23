<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class BookingAvailableDatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_uuid'   => ['required', 'string', 'exists:provider_branches,uuid'],
            'service_uuid'  => ['required', 'string', 'exists:services,uuid'],
            'employee_uuid' => ['required', 'string', 'exists:employees,uuid'],
            'month'         => ['required', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ];
    }

    public function queryParameters(): array
    {
        return [
            'branch_uuid'   => ['description' => 'UUID of the provider branch.', 'example' => '01jqs5p0000000000000000001'],
            'service_uuid'  => ['description' => 'UUID of the service.', 'example' => '01jqs5p0000000000000000002'],
            'employee_uuid' => ['description' => 'UUID of the employee.', 'example' => '01jqs5p0000000000000000003'],
            'month'         => ['description' => 'Target month in YYYY-MM format.', 'example' => '2026-04'],
        ];
    }
}
