<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'unique:providers,email'],
            'password'      => ['required', 'string', 'min:8'],
            'phone'         => ['required', 'string', 'max:20'],
            'category_uuid' => ['required', 'string', 'exists:categories,uuid'],
            'city_uuid'     => ['required', 'string', 'exists:cities,uuid'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Provider full name',
                'required' => true,
                'example' => 'Ahmed Mohamed',
            ],
            'email' => [
                'description' => 'Provider email address',
                'required' => true,
                'example' => 'provider@example.com',
            ],
            'password' => [
                'description' => 'Provider password (min 8 characters)',
                'required' => true,
                'example' => 'password123',
            ],
            'phone' => [
                'description' => 'Provider phone number',
                'required' => true,
                'example' => '+201234567890',
            ],
            'category_uuid' => [
                'description' => 'Business type / Category UUID',
                'required' => true,
                'example' => '<CATEGORY_UUID>',
            ],
            'city_uuid' => [
                'description' => 'City UUID of the provider',
                'required' => true,
                'example' => '<CITY_UUID>',
            ],
        ];
    }
}
