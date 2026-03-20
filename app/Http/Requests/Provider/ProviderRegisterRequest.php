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
            // Provider account
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'unique:providers,email'],
            'password'              => ['required', 'string', 'min:8'],
            'phone'                 => ['required', 'string', 'max:20'],
            'category_uuid'         => ['required', 'string', 'exists:categories,uuid'],

            // Main branch
            'branch'                => ['required', 'array'],
            'branch.name'           => ['required', 'string', 'max:255'],
            'branch.phone'          => ['nullable', 'string', 'max:30'],
            'branch.city_uuid'      => ['required', 'string', 'exists:cities,uuid'],
            'branch.latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'branch.longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'branch.logo'           => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],

            // Services
            'services'              => ['nullable', 'array'],
            'services.*.name'       => ['required', 'array'],
            'services.*.name.ar'    => ['required', 'string', 'max:255'],
            'services.*.name.en'    => ['required', 'string', 'max:255'],
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
            'branch.name' => [
                'description' => 'Main branch (business) name',
                'required' => true,
                'example' => 'Main Branch',
            ],
            'branch.phone' => [
                'description' => 'Branch phone number',
                'required' => false,
                'example' => '+201234567890',
            ],
            'branch.city_uuid' => [
                'description' => 'City UUID of the branch',
                'required' => true,
                'example' => '<CITY_UUID>',
            ],
            'branch.latitude' => [
                'description' => 'Branch latitude (optional)',
                'required' => false,
                'example' => 30.0444,
            ],
            'branch.longitude' => [
                'description' => 'Branch longitude (optional)',
                'required' => false,
                'example' => 31.2357,
            ],
            'branch.logo' => [
                'description' => 'Branch logo image (jpeg, png, webp, max 2MB)',
                'required' => false,
                'example' => null,
            ],
            'services' => [
                'description' => 'Array of new services to create and attach to this provider',
                'required' => false,
                'example' => [['name' => ['ar' => 'تنظيف', 'en' => 'Cleaning']]],
            ],
            'services.*.name.ar' => [
                'description' => 'Service name in Arabic',
                'required' => true,
                'example' => 'تنظيف',
            ],
            'services.*.name.en' => [
                'description' => 'Service name in English',
                'required' => true,
                'example' => 'Cleaning',
            ],
        ];
    }
}
