<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class CheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shift_date_uuid' => ['nullable', 'string', 'size:26'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'shift_date_uuid' => [
                'description' => 'UUID of the shift date to link this check-in to. The employee must be assigned to it.',
                'required'    => false,
                'example'     => '01hwz3k8m5n2q4r6s7t9v0w1xy',
            ],
            'notes' => [
                'description' => 'Optional check-in notes.',
                'required'    => false,
                'example'     => 'Arrived on time.',
            ],
        ];
    }
}
