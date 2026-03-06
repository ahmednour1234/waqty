<?php

namespace App\Http\Requests\User\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UserResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'User email address.',
                'example' => 'ahmed@example.com',
            ],
            'otp' => [
                'description' => 'Six digit OTP code.',
                'example' => '123456',
            ],
            'password' => [
                'description' => 'New password.',
                'example' => 'Password123',
            ],
            'password_confirmation' => [
                'description' => 'New password confirmation.',
                'example' => 'Password123',
            ],
        ];
    }
}
