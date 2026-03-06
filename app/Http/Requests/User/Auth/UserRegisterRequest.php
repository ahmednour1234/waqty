<?php

namespace App\Http\Requests\User\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UserRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'unique:users,phone'],
            'date_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'User full name.',
                'example' => 'Ahmed Nour',
            ],
            'email' => [
                'description' => 'User email address.',
                'example' => 'ahmed@example.com',
            ],
            'phone' => [
                'description' => 'User phone number.',
                'example' => '+201000000000',
            ],
            'date_birth' => [
                'description' => 'Birth date in Y-m-d format.',
                'example' => '1995-05-20',
            ],
            'gender' => [
                'description' => 'User gender.',
                'example' => 'male',
            ],
            'password' => [
                'description' => 'User password.',
                'example' => 'Password123',
            ],
            'password_confirmation' => [
                'description' => 'Password confirmation.',
                'example' => 'Password123',
            ],
            'image' => [
                'description' => 'Optional profile image file.',
            ],
        ];
    }
}
