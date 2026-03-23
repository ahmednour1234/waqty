<?php

namespace App\Http\Requests\User;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
            'booking_date'  => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time'    => ['required', 'date_format:H:i', 'regex:/^\d{2}:\d{2}$/'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ];
    }
}
