<?php

namespace App\Http\Requests\Admin;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;

class AdminPaymentIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['nullable', 'string', 'in:' . implode(',', Payment::ALL_METHODS)],
            'status'         => ['nullable', 'string', 'in:' . implode(',', Payment::ALL_STATUSES)],
            'booking_uuid'   => ['nullable', 'string', 'exists:bookings,uuid'],
            'provider_uuid'  => ['nullable', 'string', 'exists:providers,uuid'],
            'from_date'      => ['nullable', 'date', 'date_format:Y-m-d'],
            'to_date'        => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'trashed'        => ['nullable', 'string', 'in:only,with'],
            'per_page'       => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function queryParameters(): array
    {
        return [
            'payment_method' => ['description' => 'Filter by payment method.', 'example' => 'cash'],
            'status'         => ['description' => 'Filter by payment status.', 'example' => 'completed'],
            'booking_uuid'   => ['description' => 'Filter by booking UUID.', 'example' => null],
            'provider_uuid'  => ['description' => 'Filter by provider UUID.', 'example' => null],
            'from_date'      => ['description' => 'Start date filter (Y-m-d).', 'example' => '2026-01-01'],
            'to_date'        => ['description' => 'End date filter (Y-m-d).', 'example' => '2026-12-31'],
            'trashed'        => ['description' => 'Include soft-deleted records. Values: only, with.', 'example' => null],
            'per_page'       => ['description' => 'Number of results per page.', 'example' => 15],
        ];
    }
}
