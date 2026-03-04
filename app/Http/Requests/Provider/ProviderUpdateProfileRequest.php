<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderUpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'logo' => ['nullable', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'max:2048'],
        ];
    }
}
