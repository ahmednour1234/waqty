<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeBookingIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'       => ['nullable', 'string', 'in:pending,confirmed,completed,cancelled,no_show'],
            'booking_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'from_date'    => ['nullable', 'date', 'date_format:Y-m-d'],
            'to_date'      => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'today'        => ['nullable', 'boolean'],
            'upcoming'     => ['nullable', 'boolean'],
            'per_page'     => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
