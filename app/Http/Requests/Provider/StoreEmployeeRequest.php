<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:employees,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
            'branch_uuid' => ['required', 'string', 'exists:provider_branches,uuid'],
            'active' => ['sometimes', 'boolean'],
            'blocked' => ['sometimes', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $provider = Auth::guard('provider')->user();
        $branchUuid = $this->input('branch_uuid');
        if ($branchUuid) {
            $branch = \App\Models\ProviderBranch::whereUuid($branchUuid)->first();
            if ($branch && $branch->provider_id !== $provider->id) {
                $this->merge(['branch_uuid' => null]);
            }
        }
    }

    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Employee name',
                'required' => true,
                'example' => 'Employee Name',
            ],
            'email' => [
                'description' => 'Employee email address',
                'required' => true,
                'example' => 'employee@example.com',
            ],
            'phone' => [
                'description' => 'Employee phone number',
                'required' => false,
                'example' => '+966501234567',
            ],
            'password' => [
                'description' => 'Employee password (minimum 8 characters)',
                'required' => true,
                'example' => 'password123',
            ],
            'branch_uuid' => [
                'description' => 'Branch UUID',
                'required' => true,
                'example' => '123e4567-e89b-12d3-a456-426614174000',
            ],
            'active' => [
                'description' => 'Employee active status',
                'required' => false,
                'example' => true,
            ],
            'blocked' => [
                'description' => 'Employee blocked status',
                'required' => false,
                'example' => false,
            ],
            'logo' => [
                'description' => 'Employee logo image (jpeg, png, webp, max 2MB)',
                'required' => false,
                'example' => null,
            ],
        ];
    }
}
