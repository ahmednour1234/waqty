<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'Admin email address',
                'required' => true,
                'example' => 'admin@example.com',
            ],
            'password' => [
                'description' => 'Admin password',
                'required' => true,
                'example' => 'password123',
            ],
        ];
    }
}
