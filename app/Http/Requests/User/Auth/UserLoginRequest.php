<?php

namespace App\Http\Requests\User\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UserLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'login' => [
                'description' => 'User email address or phone number.',
                'example' => 'ahmed@example.com',
            ],
            'password' => [
                'description' => 'User password.',
                'example' => 'Password123',
            ],
        ];
    }
}
