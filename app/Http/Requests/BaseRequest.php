<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

abstract class BaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [];
    }

    protected function prepareForValidation(): void
    {
        if (Auth::check() && !$this->has('user_id')) {
            $this->merge(['user_id' => Auth::id()]);
        }
    }
}
