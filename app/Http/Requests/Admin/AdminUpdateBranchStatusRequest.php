<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateBranchStatusRequest extends FormRequest
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
            'banned' => ['sometimes', 'boolean'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'active' => [
                'description' => 'Branch active status',
                'required' => false,
                'example' => true,
            ],
            'blocked' => [
                'description' => 'Branch blocked status',
                'required' => false,
                'example' => false,
            ],
            'banned' => [
                'description' => 'Branch banned status',
                'required' => false,
                'example' => false,
            ],
        ];
    }
}
