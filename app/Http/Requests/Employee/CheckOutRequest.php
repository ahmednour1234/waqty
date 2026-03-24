<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class CheckOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'notes' => [
                'description' => 'Optional check-out notes.',
                'required'    => false,
                'example'     => 'Leaving after completing all tasks.',
            ],
        ];
    }
}
