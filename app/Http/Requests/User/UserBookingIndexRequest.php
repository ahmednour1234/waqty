<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserBookingIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'       => ['nullable', 'string', 'in:pending,confirmed,completed,cancelled,no_show'],
            'from_date'    => ['nullable', 'date', 'date_format:Y-m-d'],
            'to_date'      => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'upcoming'     => ['nullable', 'boolean'],
            'past'         => ['nullable', 'boolean'],
            'per_page'     => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function queryParameters(): array
    {
        return [
            'status'    => ['description' => 'Filter by booking status. Allowed: pending, confirmed, completed, cancelled, no_show.', 'example' => 'pending'],
            'from_date' => ['description' => 'Filter bookings on or after this date (YYYY-MM-DD).', 'example' => '2026-04-01'],
            'to_date'   => ['description' => 'Filter bookings on or before this date (YYYY-MM-DD).', 'example' => '2026-04-30'],
            'upcoming'  => ['description' => 'Set to true to show only upcoming bookings.', 'example' => true],
            'past'      => ['description' => 'Set to true to show only past bookings.', 'example' => false],
            'per_page'  => ['description' => 'Number of results per page (1–100).', 'example' => 15],
        ];
    }
}
