<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;

class StoreBranchBookingRequest extends FormRequest
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
            'booking_date'  => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time'    => ['required', 'date_format:H:i', 'regex:/^\d{2}:\d{2}$/'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'service_uuid'  => ['description' => 'UUID of the service to book.', 'example' => '01jqs5p0000000000000000002'],
            'employee_uuid' => ['description' => 'UUID of the employee who will perform the service.', 'example' => '01jqs5p0000000000000000003'],
            'booking_date'  => ['description' => 'Date of the booking (YYYY-MM-DD, today or later).', 'example' => '2026-04-15'],
            'start_time'    => ['description' => 'Start time of the booking slot (HH:MM, 24-hour).', 'example' => '10:30'],
            'notes'         => ['description' => 'Optional notes for the booking.', 'example' => 'Walk-in client.'],
        ];
    }
}
