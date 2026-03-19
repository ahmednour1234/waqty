<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminServiceStatusRequest extends FormRequest
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
                'description' => 'New active state for the service.',
                'required' => true,
                'example' => true,
            ],
        ];
    }
}
