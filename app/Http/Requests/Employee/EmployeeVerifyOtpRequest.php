<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeVerifyOtpRequest extends FormRequest
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
                'description' => 'Employee email address',
                'required' => true,
                'example' => 'employee@example.com',
            ],
            'otp' => [
                'description' => 'OTP verification code (6 digits)',
                'required' => true,
                'example' => '123456',
            ],
        ];
    }
}
