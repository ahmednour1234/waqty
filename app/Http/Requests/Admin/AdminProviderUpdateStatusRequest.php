<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminProviderUpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'active' => ['nullable', 'boolean'],
            'blocked' => ['nullable', 'boolean'],
            'banned' => ['nullable', 'boolean'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'active' => [
                'description' => 'Provider active status',
                'required' => false,
                'example' => true,
            ],
            'blocked' => [
                'description' => 'Provider blocked status',
                'required' => false,
                'example' => false,
            ],
            'banned' => [
                'description' => 'Provider banned status',
                'required' => false,
                'example' => false,
            ],
        ];
    }
}
