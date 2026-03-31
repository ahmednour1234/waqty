<?php

namespace App\Http\Requests\Provider;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_uuid'   => ['required', 'string', 'exists:bookings,uuid'],
            'payment_method' => ['required', 'string', 'in:' . implode(',', Payment::ALL_METHODS)],
            'amount'         => ['nullable', 'numeric', 'min:0'],
            'status'         => ['nullable', 'string', 'in:' . implode(',', Payment::ALL_STATUSES)],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ];
    }
}
