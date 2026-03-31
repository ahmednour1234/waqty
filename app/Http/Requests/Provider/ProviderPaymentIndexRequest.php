<?php

namespace App\Http\Requests\Provider;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;

class ProviderPaymentIndexRequest extends FormRequest
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
            'from_date'      => ['nullable', 'date', 'date_format:Y-m-d'],
            'to_date'        => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'per_page'       => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
