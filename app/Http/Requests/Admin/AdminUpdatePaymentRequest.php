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
}
