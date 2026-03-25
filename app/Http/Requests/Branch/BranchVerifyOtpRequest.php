<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;

class BranchVerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'otp'   => ['required', 'string', 'size:6'],
        ];
    }
}
