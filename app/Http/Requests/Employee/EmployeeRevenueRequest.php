<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRevenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date'   => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }

    public function queryParameters(): array
    {
        return [
            'start_date' => ['description' => 'Filter from this date (Y-m-d).', 'example' => '2026-01-01'],
            'end_date'   => ['description' => 'Filter until this date (Y-m-d).', 'example' => '2026-12-31'],
        ];
    }
}
