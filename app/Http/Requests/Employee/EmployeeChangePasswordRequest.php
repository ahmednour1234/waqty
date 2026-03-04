<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8'],
            'new_password_confirmation' => ['required', 'same:new_password'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'current_password' => [
                'description' => 'Current password',
                'required' => true,
                'example' => 'currentpassword123',
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
