<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminEmployeeFiltersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_uuid' => ['sometimes', 'string', 'exists:providers,uuid'],
            'branch_uuid' => ['sometimes', 'string', 'exists:provider_branches,uuid'],
            'active' => ['sometimes', 'boolean'],
            'blocked' => ['sometimes', 'boolean'],
            'search' => ['sometimes', 'string', 'max:255'],
            'trashed' => ['sometimes', 'string', 'in:only,with'],
        ];
    }
}
