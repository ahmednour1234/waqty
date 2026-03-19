<?php

namespace App\Http\Requests\User\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UserVerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'min:4', 'max:6'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'User email address.',
                'required' => true,
                'example' => 'user@example.com',
            ],
            'otp' => [
                'description' => 'Verification OTP code sent to email.',
                'required' => true,
                'example' => '123456',
            ],
        ];
    }
}
