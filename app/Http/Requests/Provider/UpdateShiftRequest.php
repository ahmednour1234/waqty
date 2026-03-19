<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes'       => ['sometimes', 'nullable', 'string', 'max:5000'],
            'active'      => ['sometimes', 'boolean'],
            'branch_uuid' => ['sometimes', 'nullable', 'string', 'exists:provider_branches,uuid'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'title'       => ['description' => 'Shift title',    'required' => false, 'example' => 'Updated Title'],
            'notes'       => ['description' => 'Internal notes', 'required' => false, 'example' => null],
            'active'      => ['description' => 'Active status',  'required' => false, 'example' => true],
            'branch_uuid' => ['description' => 'Branch UUID (null to unset)', 'required' => false, 'example' => null],
        ];
    }
}
