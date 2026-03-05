<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderVerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
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
        ];
    }
}
