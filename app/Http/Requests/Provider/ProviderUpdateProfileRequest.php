<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderUpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'logo' => ['nullable', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'max:2048'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Provider name',
                'required' => true,
                'example' => 'Provider Name',
            ],
            'phone' => [
                'description' => 'Provider phone number',
                'required' => false,
                'example' => '+966501234567',
            ],
            'category_id' => [
                'description' => 'Category ID',
                'required' => false,
                'example' => 1,
            ],
            'city_id' => [
                'description' => 'City ID',
                'required' => true,
                'example' => 1,
            ],
            'logo' => [
                'description' => 'Provider logo image (jpeg, png, webp, max 2MB)',
                'required' => false,
                'example' => null,
            ],
        ];
    }
}
