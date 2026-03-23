<?php

namespace App\Http\Requests\Provider;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderBookingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:confirmed,completed,cancelled,no_show'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'status' => ['description' => 'New booking status. Allowed: confirmed, completed, cancelled, no_show.', 'example' => 'confirmed'],
        ];
    }
}
