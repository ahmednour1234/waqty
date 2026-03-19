<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminServiceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_uuid'      => ['nullable', 'string'],
            'sub_category_uuid'  => ['nullable', 'string'],
            'active'             => ['nullable', 'boolean'],
            'trashed'            => ['nullable', 'in:only,with'],
            'search'             => ['nullable', 'string', 'max:255'],
            'per_page'           => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
