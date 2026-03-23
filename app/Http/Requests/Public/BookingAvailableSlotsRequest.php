<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class BookingAvailableSlotsRequest extends FormRequest
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
            'date'          => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
        ];
    }
}
