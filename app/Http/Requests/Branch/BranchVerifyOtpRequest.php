<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;

class BranchVerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'otp'   => ['required', 'string', 'size:6'],
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
                'description' => '6-digit OTP sent to the branch email (use 111111 in test environment)',
                'example'     => '111111',
            ],
        ];
    }
}
