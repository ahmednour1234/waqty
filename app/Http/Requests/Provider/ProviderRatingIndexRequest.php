<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderRatingIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_uuid' => ['nullable', 'string', 'exists:bookings,uuid'],
            'employee_uuid' => ['nullable', 'string', 'exists:employees,uuid'],
            'branch_uuid' => ['nullable', 'string', 'exists:provider_branches,uuid'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'active' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
