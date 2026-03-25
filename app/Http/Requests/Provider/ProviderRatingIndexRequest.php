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

    public function bodyParameters(): array
    {
        return [
            'booking_uuid' => [
                'description' => 'Filter by booking UUID.',
                'example' => '01JABCDEF1234567890ABCDEFGH',
            ],
            'employee_uuid' => [
                'description' => 'Filter by employee UUID.',
                'example' => '01JEMPLOYE1234567890ABCDEFG',
            ],
            'branch_uuid' => [
                'description' => 'Filter by branch UUID.',
                'example' => '01JBRANCH1234567890ABCDEFGH',
            ],
            'from_date' => [
                'description' => 'Filter ratings from this date (Y-m-d).',
                'example' => '2026-01-01',
            ],
            'to_date' => [
                'description' => 'Filter ratings to this date (Y-m-d).',
                'example' => '2026-12-31',
            ],
            'rating' => [
                'description' => 'Filter by exact star value (1-5).',
                'example' => 5,
            ],
            'active' => [
                'description' => 'Filter by rating active status.',
                'example' => true,
            ],
            'per_page' => [
                'description' => 'Items per page.',
                'example' => 15,
            ],
        ];
    }
}
