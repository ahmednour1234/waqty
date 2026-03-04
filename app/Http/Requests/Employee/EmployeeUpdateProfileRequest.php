<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeUpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Employee name',
                'required' => false,
                'example' => 'Employee Name',
            ],
            'phone' => [
                'description' => 'Employee phone number',
                'required' => false,
                'example' => '+966501234567',
            ],
            'logo' => [
                'description' => 'Employee logo image (jpeg, png, webp, max 2MB)',
                'required' => false,
                'example' => null,
            ],
        ];
    }
}
