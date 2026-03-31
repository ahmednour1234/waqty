<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderBookingGridRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date'        => ['required', 'date', 'date_format:Y-m-d'],
            'branch_uuid' => ['nullable', 'string', 'exists:provider_branches,uuid'],
        ];
    }

    public function queryParameters(): array
    {
        return [
            'date'        => ['description' => 'The date to build the schedule grid for (YYYY-MM-DD).', 'example' => '2026-04-15'],
            'branch_uuid' => ['description' => 'Optional: filter to a specific branch UUID.', 'example' => null],
        ];
    }
}
