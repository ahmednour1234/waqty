<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserBookingIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'       => ['nullable', 'string', 'in:pending,confirmed,completed,cancelled,no_show'],
            'from_date'    => ['nullable', 'date', 'date_format:Y-m-d'],
            'to_date'      => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'upcoming'     => ['nullable', 'boolean'],
            'past'         => ['nullable', 'boolean'],
            'per_page'     => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
