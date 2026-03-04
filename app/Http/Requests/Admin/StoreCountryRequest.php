<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCountryRequest extends FormRequest
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
            'iso2' => ['nullable', 'string', 'size:2'],
            'phone_code' => ['nullable', 'string', 'max:10'],
            'active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Country name in Arabic and English',
                'required' => true,
                'example' => ['ar' => 'اسم البلد', 'en' => 'Country Name'],
            ],
            'iso2' => [
                'description' => 'ISO 2-letter country code',
                'required' => false,
                'example' => 'SA',
            ],
            'phone_code' => [
                'description' => 'Country phone code',
                'required' => false,
                'example' => '+966',
            ],
            'active' => [
                'description' => 'Country active status',
                'required' => false,
                'example' => true,
            ],
            'sort_order' => [
                'description' => 'Country sort order',
                'required' => false,
                'example' => 1,
            ],
        ];
    }
}
