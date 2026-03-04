<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminEmployeeFiltersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_uuid' => ['sometimes', 'string', 'exists:providers,uuid'],
            'branch_uuid' => ['sometimes', 'string', 'exists:provider_branches,uuid'],
            'active' => ['sometimes', 'boolean'],
            'blocked' => ['sometimes', 'boolean'],
            'search' => ['sometimes', 'string', 'max:255'],
            'trashed' => ['sometimes', 'string', 'in:only,with'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'provider_uuid' => [
                'description' => 'Filter by provider UUID',
                'required' => false,
                'example' => '123e4567-e89b-12d3-a456-426614174000',
            ],
            'branch_uuid' => [
                'description' => 'Filter by branch UUID',
                'required' => false,
                'example' => '123e4567-e89b-12d3-a456-426614174000',
            ],
            'active' => [
                'description' => 'Filter by active status',
                'required' => false,
                'example' => true,
            ],
            'blocked' => [
                'description' => 'Filter by blocked status',
                'required' => false,
                'example' => false,
            ],
            'search' => [
                'description' => 'Search term',
                'required' => false,
                'example' => 'employee name',
            ],
            'trashed' => [
                'description' => 'Include trashed records (only, with)',
                'required' => false,
                'example' => 'only',
            ],
        ];
    }
}
