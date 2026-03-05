<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:4'],
            'new_password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[a-zA-Z])(?=.*[0-9]).+$/'],
            'new_password_confirmation' => ['required', 'same:new_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_password.regex' => 'The password must contain at least one letter and one number.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'Provider email address',
                'required' => true,
                'example' => 'provider@example.com',
            ],
            'otp' => [
                'description' => 'OTP verification code (6 digits)',
                'required' => true,
                'example' => '123456',
            ],
            'new_password' => [
                'description' => 'New password (minimum 8 characters, must contain at least one letter and one number)',
                'required' => true,
                'example' => 'newpassword123',
            ],
            'new_password_confirmation' => [
                'description' => 'New password confirmation',
                'required' => true,
                'example' => 'newpassword123',
            ],
        ];
    }
}
