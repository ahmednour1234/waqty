<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeResetPasswordRequest extends FormRequest
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
            'new_password' => ['required', 'string', 'min:8'],
            'new_password_confirmation' => ['required', 'same:new_password'],
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
                'description' => 'One-time password (6 digits)',
                'required' => true,
                'example' => '123456',
            ],
            'new_password' => [
                'description' => 'New password (minimum 8 characters)',
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
