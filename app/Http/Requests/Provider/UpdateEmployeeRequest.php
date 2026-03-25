<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeUuid = $this->route('uuid');
        $employee = $employeeUuid ? \App\Models\Employee::whereUuid($employeeUuid)->first() : null;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:employees,email,' . ($employee?->id ?? 0)],
            'phone' => ['nullable', 'string', 'max:30'],
            'branch_uuid' => ['sometimes', 'string', 'exists:provider_branches,uuid'],
            'active' => ['sometimes', 'boolean'],
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
                'required' => false,
                'example' => 'Employee Name',
            ],
            'job_title' => [
                'description' => 'Employee job title',
                'required' => false,
                'example' => 'Senior Technician',
            ],
            'email' => [
                'description' => 'Employee email address',
                'required' => false,
                'example' => 'employee@example.com',
            ],
            'phone' => [
                'description' => 'Employee phone number',
                'required' => false,
                'example' => '+966501234567',
            ],
            'branch_uuid' => [
                'description' => 'Branch UUID',
                'required' => false,
                'example' => '123e4567-e89b-12d3-a456-426614174000',
            ],
            'active' => [
                'description' => 'Employee active status',
                'required' => false,
                'example' => true,
            ],
            'logo' => [
                'description' => 'Employee logo image (jpeg, png, webp, max 2MB)',
                'required' => false,
                'example' => null,
            ],
        ];
    }
}
