<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeLoginRequest extends FormRequest
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
                'description' => 'Employee email address',
                'required' => true,
                'example' => 'employee@example.com',
            ],
            'password' => [
                'description' => 'Employee password',
                'required' => true,
                'example' => 'password123',
            ],
        ];
    }
}
