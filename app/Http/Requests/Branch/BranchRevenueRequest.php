<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;

class BranchRevenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date'    => ['nullable', 'date_format:Y-m-d'],
            'end_date'      => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'employee_uuid' => ['nullable', 'string', 'exists:employees,uuid'],
        ];
    }

    public function queryParameters(): array
    {
        return [
            'start_date'    => ['description' => 'Filter from this date (Y-m-d).', 'example' => '2026-01-01'],
            'end_date'      => ['description' => 'Filter until this date (Y-m-d).', 'example' => '2026-12-31'],
            'employee_uuid' => ['description' => 'Filter by a specific employee UUID.', 'example' => '01JABCDEF1234567890ABCDEFGH'],
        ];
    }
}
