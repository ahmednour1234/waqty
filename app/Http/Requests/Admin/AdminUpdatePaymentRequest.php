<?php

namespace App\Http\Requests\Admin;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;

class AdminUpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['sometimes', 'string', 'in:' . implode(',', Payment::ALL_METHODS)],
            'amount'         => ['sometimes', 'numeric', 'min:0'],
            'status'         => ['sometimes', 'string', 'in:' . implode(',', Payment::ALL_STATUSES)],
            'transaction_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes'          => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'payment_method' => ['description' => 'Payment method: cash or paymob.', 'example' => 'paymob'],
            'amount'         => ['description' => 'Payment amount.', 'example' => 200.00],
            'status'         => ['description' => 'Payment status: pending, completed, failed, or refunded.', 'example' => 'completed'],
            'transaction_id' => ['description' => 'External transaction reference.', 'example' => 'TXN-123456'],
            'notes'          => ['description' => 'Optional notes.', 'example' => null],
        ];
    }
}
