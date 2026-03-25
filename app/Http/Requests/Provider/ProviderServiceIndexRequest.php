<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderServiceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sub_category_uuid' => ['nullable', 'string'],
            'category'          => ['nullable', 'string', 'max:255'],
            'active'            => ['nullable', 'boolean'],
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
            'category' => [
                'description' => 'Filter by category name (plain string), e.g. Hair or Skin.',
                'required' => false,
                'example' => 'Hair',
            ],
            'active' => [
                'description' => 'Filter by active status.',
                'required' => false,
                'example' => true,
            ],
            'search' => [
                'description' => 'Search term for service name/description.',
                'required' => false,
                'example' => 'repair',
            ],
            'per_page' => [
                'description' => 'Items per page (1-100).',
                'required' => false,
                'example' => 15,
            ],
        ];
    }
}
