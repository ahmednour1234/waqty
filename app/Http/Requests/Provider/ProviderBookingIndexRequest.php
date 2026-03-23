<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderBookingIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'        => ['nullable', 'string', 'in:pending,confirmed,completed,cancelled,no_show'],
            'branch_uuid'   => ['nullable', 'string', 'exists:provider_branches,uuid'],
            'employee_uuid' => ['nullable', 'string', 'exists:employees,uuid'],
            'booking_date'  => ['nullable', 'date', 'date_format:Y-m-d'],
            'from_date'     => ['nullable', 'date', 'date_format:Y-m-d'],
            'to_date'       => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'per_page'      => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function queryParameters(): array
    {
        return [
            'status'        => ['description' => 'Filter by booking status. Allowed: pending, confirmed, completed, cancelled, no_show.', 'example' => 'confirmed'],
            'branch_uuid'   => ['description' => 'Filter by branch UUID.', 'example' => '01jqs5p0000000000000000001'],
            'employee_uuid' => ['description' => 'Filter by employee UUID.', 'example' => '01jqs5p0000000000000000003'],
            'booking_date'  => ['description' => 'Filter by exact booking date (YYYY-MM-DD).', 'example' => '2026-04-15'],
            'from_date'     => ['description' => 'Filter bookings on or after this date (YYYY-MM-DD).', 'example' => '2026-04-01'],
            'to_date'       => ['description' => 'Filter bookings on or before this date (YYYY-MM-DD).', 'example' => '2026-04-30'],
            'per_page'      => ['description' => 'Number of results per page (1–100).', 'example' => 15],
        ];
    }
}
