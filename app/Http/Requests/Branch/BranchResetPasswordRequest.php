<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;

class BranchResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'                    => ['required', 'email'],
            'otp'                      => ['required', 'string', 'size:6'],
            'new_password'             => ['required', 'string', 'min:8'],
            'new_password_confirmation' => ['required', 'same:new_password'],
        ];
    }
}
