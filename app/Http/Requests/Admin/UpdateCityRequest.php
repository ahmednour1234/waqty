<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country_uuid'     => ['sometimes', 'string', 'exists:countries,uuid'],
            'governorate_uuid' => ['nullable', 'string', 'exists:governorates,uuid'],
            'name.ar'          => ['sometimes', 'string', 'max:255'],
            'name.en'          => ['sometimes', 'string', 'max:255'],
            'active'           => ['sometimes', 'boolean'],
            'sort_order'       => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'country_uuid' => [
                'description' => 'Country UUID',
                'required' => false,
                'example' => '123e4567-e89b-12d3-a456-426614174000',
            ],
            'name' => [
                'description' => 'City name in Arabic and English',
                'required' => false,
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
