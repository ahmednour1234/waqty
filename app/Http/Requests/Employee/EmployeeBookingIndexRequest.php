<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeBookingIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'       => ['nullable', 'string', 'in:pending,confirmed,completed,cancelled,no_show'],
            'booking_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'from_date'    => ['nullable', 'date', 'date_format:Y-m-d'],
            'to_date'      => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'today'        => ['nullable', 'boolean'],
            'upcoming'     => ['nullable', 'boolean'],
            'per_page'     => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function queryParameters(): array
    {
        return [
            'status'       => ['description' => 'Filter by booking status. Allowed: pending, confirmed, completed, cancelled, no_show.', 'example' => 'confirmed'],
            'booking_date' => ['description' => 'Filter by exact booking date (YYYY-MM-DD).', 'example' => '2026-04-15'],
            'from_date'    => ['description' => 'Filter bookings on or after this date (YYYY-MM-DD).', 'example' => '2026-04-01'],
            'to_date'      => ['description' => 'Filter bookings on or before this date (YYYY-MM-DD).', 'example' => '2026-04-30'],
            'today'        => ['description' => "Set to true to show only today's bookings.", 'example' => true],
            'upcoming'     => ['description' => 'Set to true to show only upcoming bookings.', 'example' => false],
            'per_page'     => ['description' => 'Number of results per page (1–100).', 'example' => 15],
        ];
    }
}
