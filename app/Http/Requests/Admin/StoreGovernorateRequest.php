<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreGovernorateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name.ar'    => ['required', 'string', 'max:255'],
            'name.en'    => ['required', 'string', 'max:255'],
            'active'     => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Governorate name in Arabic and English',
                'required' => true,
                'example' => ['ar' => 'القاهرة', 'en' => 'Cairo'],
            ],
            'active' => [
                'description' => 'Governorate active status',
                'required' => false,
                'example' => true,
            ],
            'sort_order' => [
                'description' => 'Sort order',
                'required' => false,
                'example' => 1,
            ],
        ];
    }
}
