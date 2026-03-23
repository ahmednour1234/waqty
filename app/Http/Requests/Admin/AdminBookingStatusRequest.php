<?php

namespace App\Http\Requests\Admin;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class AdminBookingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pending,confirmed,completed,cancelled,no_show'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'status' => ['description' => 'New booking status. Allowed: pending, confirmed, completed, cancelled, no_show.', 'example' => 'confirmed'],
        ];
    }
}
