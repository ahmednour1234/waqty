<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country_uuid' => ['required', 'string', 'exists:countries,uuid'],
            'name.ar' => ['required', 'string', 'max:255'],
            'name.en' => ['required', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'country_uuid' => [
                'description' => 'Country UUID',
                'required' => true,
                'example' => '123e4567-e89b-12d3-a456-426614174000',
            ],
            'name' => [
                'description' => 'City name in Arabic and English',
                'required' => true,
                'example' => ['ar' => 'اسم المدينة', 'en' => 'City Name'],
            ],
            'active' => [
                'description' => 'City active status',
                'required' => false,
                'example' => true,
            ],
            'sort_order' => [
                'description' => 'City sort order',
                'required' => false,
                'example' => 1,
            ],
        ];
    }
}
