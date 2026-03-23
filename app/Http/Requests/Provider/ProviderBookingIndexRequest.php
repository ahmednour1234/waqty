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
}
