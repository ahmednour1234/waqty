<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminContentPageUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug'       => ['sometimes', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'title_en'   => ['sometimes', 'string', 'max:255'],
            'title_ar'   => ['sometimes', 'string', 'max:255'],
            'content_en' => ['nullable', 'string'],
            'content_ar' => ['nullable', 'string'],
        ];
    }
}
