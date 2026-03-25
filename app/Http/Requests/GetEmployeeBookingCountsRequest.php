<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetEmployeeBookingCountsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'branch_uuid' => ['nullable', 'string', 'exists:provider_branches,uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.date_format' => 'Start date must be in Y-m-d format',
            'end_date.date_format' => 'End date must be in Y-m-d format',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
            'branch_uuid.exists' => 'The selected branch does not exist',
        ];
    }
}
