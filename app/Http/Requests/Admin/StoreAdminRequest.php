<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8'],
            'active' => ['sometimes', 'boolean'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Admin name',
                'required' => true,
                'example' => 'John Doe',
            ],
            'email' => [
                'description' => 'Admin email address',
                'required' => true,
                'example' => 'admin@example.com',
            ],
            'password' => [
                'description' => 'Admin password (minimum 8 characters)',
                'required' => true,
                'example' => 'password123',
            ],
            'active' => [
                'description' => 'Admin active status',
                'required' => false,
                'example' => true,
            ],
        ];
    }
}
