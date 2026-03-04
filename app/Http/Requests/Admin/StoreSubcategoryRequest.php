<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubcategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_uuid' => ['required', 'string', 'exists:categories,uuid'],
            'name.ar' => ['required', 'string', 'max:255'],
            'name.en' => ['required', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer'],
            'image' => ['sometimes', 'file', 'mimes:jpeg,png,webp', 'max:2048', 'mimetypes:image/jpeg,image/png,image/webp'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'category_uuid' => [
                'description' => 'Category UUID',
                'required' => true,
                'example' => '123e4567-e89b-12d3-a456-426614174000',
            ],
            'name' => [
                'description' => 'Subcategory name in Arabic and English',
                'required' => true,
                'example' => ['ar' => 'اسم الفئة الفرعية', 'en' => 'Subcategory Name'],
            ],
            'active' => [
                'description' => 'Subcategory active status',
                'required' => false,
                'example' => true,
            ],
            'sort_order' => [
                'description' => 'Subcategory sort order',
                'required' => false,
                'example' => 1,
            ],
            'image' => [
                'description' => 'Subcategory image file (jpeg, png, webp, max 2MB)',
                'required' => false,
                'example' => null,
            ],
        ];
    }
}
