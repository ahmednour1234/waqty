<?php

namespace App\Http\Resources\Provider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'              => $this->uuid,
            'name'              => $this->name,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'total_bookings'    => (int) $this->total_bookings,
            'last_booking_date' => $this->last_booking_date,
        ];
    }
}
