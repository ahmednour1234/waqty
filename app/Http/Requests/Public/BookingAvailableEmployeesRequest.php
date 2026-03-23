<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class BookingAvailableEmployeesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_uuid' => ['required', 'string', 'exists:services,uuid'],
            'branch_uuid'  => ['required', 'string', 'exists:provider_branches,uuid'],
        ];
    }
}
