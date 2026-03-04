<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
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
            'name' => [
                'description' => 'Category name in Arabic and English',
                'required' => true,
                'example' => ['ar' => 'اسم الفئة', 'en' => 'Category Name'],
            ],
            'active' => [
                'description' => 'Category active status',
                'required' => false,
                'example' => true,
            ],
            'sort_order' => [
                'description' => 'Category sort order',
                'required' => false,
                'example' => 1,
            ],
            'image' => [
                'description' => 'Category image file (jpeg, png, webp, max 2MB)',
                'required' => false,
                'example' => null,
            ],
        ];
    }
}
