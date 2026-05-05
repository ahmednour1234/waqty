<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminAnnouncementStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title_en'   => ['required', 'string', 'max:255'],
            'title_ar'   => ['required', 'string', 'max:255'],
            'message_en' => ['required', 'string'],
            'message_ar' => ['required', 'string'],
            'target'     => ['nullable', Rule::in(['all', 'users', 'providers', 'employees', 'branches'])],
            'priority'   => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'active'     => ['nullable', 'boolean'],
            'ends_at'    => ['nullable', 'date', 'after:now'],
        ];
    }
}
