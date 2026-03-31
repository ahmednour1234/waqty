<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderRevenueRequest extends FormRequest
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
            'branch_uuid'   => ['nullable', 'string', 'exists:provider_branches,uuid'],
            'employee_uuid' => ['nullable', 'string', 'exists:employees,uuid'],
        ];
    }

    public function queryParameters(): array
    {
        return [
            'start_date'    => ['description' => 'Filter from this date (Y-m-d).', 'example' => '2026-01-01'],
            'end_date'      => ['description' => 'Filter until this date (Y-m-d).', 'example' => '2026-12-31'],
            'branch_uuid'   => ['description' => 'Filter by a specific branch UUID.', 'example' => '01JABCDEF1234567890ABCDEFGH'],
            'employee_uuid' => ['description' => 'Filter by a specific employee UUID.', 'example' => '01JABCDEF1234567890ABCDEFGH'],
        ];
    }
}
