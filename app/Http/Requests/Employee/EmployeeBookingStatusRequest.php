<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeBookingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:completed,no_show'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'status' => ['description' => 'New booking status. Allowed: completed, no_show.', 'example' => 'completed'],
        ];
    }
}
