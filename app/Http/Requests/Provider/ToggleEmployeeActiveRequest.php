<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ToggleEmployeeActiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'active' => ['required', 'boolean'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'active' => [
                'description' => 'Employee active status',
                'required' => true,
                'example' => true,
            ],
        ];
    }
}
