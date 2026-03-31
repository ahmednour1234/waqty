<?php

namespace App\Http\Requests\Branch;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;

class StoreBranchPaymentRequest extends FormRequest
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

    public function bodyParameters(): array
    {
        return [
            'booking_uuid'   => ['description' => 'UUID of the booking this payment belongs to.', 'example' => '01HXZ...'],
            'payment_method' => ['description' => 'Payment method: cash or paymob.', 'example' => 'cash'],
            'amount'         => ['description' => 'Payment amount. Defaults to the booking price if omitted.', 'example' => 150.00],
            'status'         => ['description' => 'Payment status. Defaults to pending.', 'example' => 'pending'],
            'transaction_id' => ['description' => 'External transaction reference (e.g. Paymob order ID).', 'example' => null],
            'notes'          => ['description' => 'Optional notes about the payment.', 'example' => null],
        ];
    }
}
