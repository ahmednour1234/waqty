<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminAnnouncementUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title_en'   => ['sometimes', 'string', 'max:255'],
            'title_ar'   => ['sometimes', 'string', 'max:255'],
            'message_en' => ['sometimes', 'string'],
            'message_ar' => ['sometimes', 'string'],
            'target'     => ['sometimes', Rule::in(['all', 'users', 'providers', 'employees', 'branches'])],
            'priority'   => ['sometimes', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'ends_at'    => ['nullable', 'date', 'after:now'],
        ];
    }
}
