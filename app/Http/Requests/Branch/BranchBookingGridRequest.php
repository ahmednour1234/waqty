<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;

class BranchBookingGridRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'date_format:Y-m-d'],
        ];
    }

    public function queryParameters(): array
    {
        return [
            'date' => ['description' => 'The date to build the schedule grid for (YYYY-MM-DD).', 'example' => '2026-04-15'],
        ];
    }
}
