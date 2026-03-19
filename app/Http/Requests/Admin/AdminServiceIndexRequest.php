<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminServiceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_uuid'      => ['nullable', 'string'],
            'sub_category_uuid'  => ['nullable', 'string'],
            'active'             => ['nullable', 'boolean'],
            'trashed'            => ['nullable', 'in:only,with'],
            'search'             => ['nullable', 'string', 'max:255'],
            'per_page'           => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'provider_uuid' => [
                'description' => 'Filter by provider UUID.',
                'required' => false,
                'example' => '123e4567-e89b-12d3-a456-426614174000',
            ],
            'sub_category_uuid' => [
                'description' => 'Filter by subcategory UUID.',
                'required' => false,
                'example' => '123e4567-e89b-12d3-a456-426614174111',
            ],
            'active' => [
                'description' => 'Filter by active status.',
                'required' => false,
                'example' => true,
            ],
            'trashed' => [
                'description' => 'Trashed filter: only or with.',
                'required' => false,
                'example' => 'with',
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
