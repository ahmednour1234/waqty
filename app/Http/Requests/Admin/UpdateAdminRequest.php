<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:admins,email,' . $id],
            'password' => ['nullable', 'string', 'min:8'],
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
                'required' => false,
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
