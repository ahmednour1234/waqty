<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeServiceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sub_category_uuid' => ['nullable', 'string'],
            'search'            => ['nullable', 'string', 'max:255'],
            'per_page'          => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'sub_category_uuid' => [
                'description' => 'Filter by subcategory UUID.',
                'required' => false,
                'example' => '123e4567-e89b-12d3-a456-426614174111',
            ],
            'search' => [
                'description' => 'Search term for service name/description.',
                'required' => false,
                'example' => 'cleaning',
            ],
            'per_page' => [
                'description' => 'Items per page (1-100).',
                'required' => false,
                'example' => 15,
            ],
        ];
    }
}
