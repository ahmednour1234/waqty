<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'rating' => [
                'description' => 'Star rating value from 1 to 5.',
                'example' => 5,
            ],
            'comment' => [
                'description' => 'Optional feedback comment.',
                'example' => 'Great service and very professional.',
            ],
            'active' => [
                'description' => 'Optional flag to show or hide this rating.',
                'example' => true,
            ],
        ];
    }
}
