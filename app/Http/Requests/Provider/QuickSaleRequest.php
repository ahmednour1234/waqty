<?php

namespace App\Http\Requests\Provider;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;

class QuickSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Required
            'service_uuid'     => ['required', 'string', 'exists:services,uuid'],

            // Optional scoping
            'branch_uuid'      => ['nullable', 'string', 'exists:provider_branches,uuid'],
            'employee_uuid'    => ['nullable', 'string', 'exists:employees,uuid'],

            // Optional client — either existing user or walk-in
            'user_uuid'        => ['nullable', 'string', 'exists:users,uuid'],
            'user_name'        => ['nullable', 'string', 'max:255'],
            'user_phone'       => ['nullable', 'string', 'max:30'],

            // Optional price override
            'price'            => ['nullable', 'numeric', 'min:0'],

            // Optional date/time (defaults to now)
            'booking_date'     => ['nullable', 'date', 'date_format:Y-m-d'],
            'booking_time'     => ['nullable', 'date_format:H:i'],

            // Optional payment
            'payment_method'   => ['nullable', 'string', 'in:' . implode(',', Payment::ALL_METHODS)],
            'payment_amount'   => ['nullable', 'numeric', 'min:0'],

            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }
}
