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
}
