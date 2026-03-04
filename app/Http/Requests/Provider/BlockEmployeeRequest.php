<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class BlockEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'blocked' => ['required', 'boolean'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'blocked' => [
                'description' => 'Employee blocked status',
                'required' => true,
                'example' => false,
            ],
        ];
    }
}
