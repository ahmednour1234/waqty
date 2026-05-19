<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_uuid'  => ['required', 'string', 'exists:services,uuid'],
            'employee_uuid' => ['required', 'string', 'exists:employees,uuid'],
            'booking_date'  => ['required', 'date', 'date_format:Y-m-d'],
            'start_time'    => ['required', 'date_format:H:i'],
            'branch_uuid'   => ['nullable', 'string', 'exists:provider_branches,uuid'],
            'user_name'     => ['nullable', 'string', 'max:255'],
            'user_phone'    => ['nullable', 'string', 'max:30'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ];
    }
}
