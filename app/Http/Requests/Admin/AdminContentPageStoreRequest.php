<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminContentPageStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug'       => ['required', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'title_en'   => ['required', 'string', 'max:255'],
            'title_ar'   => ['required', 'string', 'max:255'],
            'content_en' => ['nullable', 'string'],
            'content_ar' => ['nullable', 'string'],
            'active'     => ['nullable', 'boolean'],
        ];
    }
}
