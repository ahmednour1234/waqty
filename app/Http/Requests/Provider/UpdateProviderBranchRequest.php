<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'city_uuid' => ['sometimes', 'string', 'exists:cities,uuid'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'active' => ['sometimes', 'boolean'],
            'is_main' => ['sometimes', 'boolean'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Branch name',
                'required' => false,
                'example' => 'Branch Name',
            ],
            'phone' => [
                'description' => 'Branch phone number',
                'required' => false,
                'example' => '+966501234567',
            ],
            'city_uuid' => [
                'description' => 'City UUID',
                'required' => false,
                'example' => '123e4567-e89b-12d3-a456-426614174000',
            ],
            'latitude' => [
                'description' => 'Branch latitude',
                'required' => false,
                'example' => 24.7136,
            ],
            'longitude' => [
                'description' => 'Branch longitude',
                'required' => false,
                'example' => 46.6753,
            ],
            'logo' => [
                'description' => 'Branch logo image (jpeg, png, webp, max 2MB)',
                'required' => false,
                'example' => null,
            ],
            'active' => [
                'description' => 'Branch active status',
                'required' => false,
                'example' => true,
            ],
            'is_main' => [
                'description' => 'Whether this is the main branch',
                'required' => false,
                'example' => false,
            ],
        ];
    }
}
