<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminUserUpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'active'  => ['nullable', 'boolean'],
            'blocked' => ['nullable', 'boolean'],
            'banned'  => ['nullable', 'boolean'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'active' => [
                'description' => 'User active status',
                'required'    => false,
                'example'     => true,
            ],
            'blocked' => [
                'description' => 'User blocked status',
                'required'    => false,
                'example'     => false,
            ],
            'banned' => [
                'description' => 'User banned status',
                'required'    => false,
                'example'     => false,
            ],
        ];
    }
}
