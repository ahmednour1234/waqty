<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ToggleShiftTemplateActiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'active' => ['required', 'boolean'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'active' => ['description' => 'Set template active status', 'required' => true, 'example' => true],
        ];
    }
}
