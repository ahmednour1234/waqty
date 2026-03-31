<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_uuid'   => ['nullable', 'string', 'exists:provider_branches,uuid'],
            'employee_uuid' => ['nullable', 'string', 'exists:employees,uuid'],
        ];
    }

    public function queryParameters(): array
    {
        return [
            'branch_uuid'   => ['description' => 'Filter by a specific branch UUID.', 'example' => '01JABCDEF1234567890ABCDEFGH'],
            'employee_uuid' => ['description' => 'Filter by a specific employee UUID.', 'example' => '01JABCDEF1234567890ABCDEFGH'],
        ];
    }
}
