<?php

namespace App\Http\Resources\Provider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One row in the client statements list.
 * $resource is a User model instance with statement aggregate columns appended.
 */
class ProviderClientStatementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalCharged = (float) ($this->total_charged ?? 0);
        $totalPaid    = (float) ($this->total_paid    ?? 0);

        return [
            'uuid'               => $this->uuid,
            'name'               => $this->name,
            'email'              => $this->email,
            'phone'              => $this->phone,
            'total_bookings'     => (int) ($this->total_bookings    ?? 0),
            'completed_bookings' => (int) ($this->completed_bookings ?? 0),
            'cancelled_bookings' => (int) ($this->cancelled_bookings ?? 0),
            'total_charged'      => $totalCharged,
            'total_paid'         => $totalPaid,
            'outstanding'        => round($totalCharged - $totalPaid, 2),
            'last_booking_date'  => $this->last_booking_date,
        ];
    }
}
