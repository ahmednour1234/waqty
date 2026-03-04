<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ProviderForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $key = 'forgot_password:' . $this->ip() . ':' . $this->input('email');
        
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        \Illuminate\Support\Facades\RateLimiter::hit($key, 60);
    }

    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'Provider email address',
                'required' => true,
                'example' => 'provider@example.com',
            ],
        ];
    }
}
