<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;

class BranchResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'                    => ['required', 'email'],
            'otp'                      => ['required', 'string', 'size:6'],
            'new_password'             => ['required', 'string', 'min:8'],
            'new_password_confirmation' => ['required', 'same:new_password'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'Branch email address',
                'example'     => 'branch@example.com',
            ],
            'otp' => [
                'description' => '6-digit OTP received by email (use 111111 in test environment)',
                'example'     => '111111',
            ],
            'new_password' => [
                'description' => 'New password (minimum 8 characters)',
                'example'     => 'newSecret123',
            ],
            'new_password_confirmation' => [
                'description' => 'Must match new_password',
                'example'     => 'newSecret123',
            ],
        ];
    }
}
