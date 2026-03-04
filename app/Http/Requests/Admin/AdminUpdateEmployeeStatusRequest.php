<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateEmployeeStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'active' => ['sometimes', 'boolean'],
            'blocked' => ['sometimes', 'boolean'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'active' => [
                'description' => 'Employee active status',
                'required' => false,
                'example' => true,
            ],
            'blocked' => [
                'description' => 'Employee blocked status',
                'required' => false,
                'example' => false,
            ],
        ];
    }
}
