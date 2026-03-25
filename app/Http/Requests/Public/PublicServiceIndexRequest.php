<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class PublicServiceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_uuid'     => ['nullable', 'string'],
            'sub_category_uuid' => ['nullable', 'string'],
            'category'          => ['nullable', 'string', 'max:255'],
            'search'            => ['nullable', 'string', 'max:255'],
            'per_page'          => ['nullable', 'integer', 'min:1', 'max:100'],
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
            'category' => [
                'description' => 'Filter by category name (plain string), e.g. Hair or Skin.',
                'required' => false,
                'example' => 'Hair',
            ],
            'search' => [
                'description' => 'Search term for service name/description.',
                'required' => false,
                'example' => 'delivery',
            ],
            'per_page' => [
                'description' => 'Items per page (1-100).',
                'required' => false,
                'example' => 15,
            ],
        ];
    }
}
