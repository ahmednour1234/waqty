<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderServiceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sub_category_uuid' => ['nullable', 'string'],
            'active'            => ['nullable', 'boolean'],
            'search'            => ['nullable', 'string', 'max:255'],
            'per_page'          => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
