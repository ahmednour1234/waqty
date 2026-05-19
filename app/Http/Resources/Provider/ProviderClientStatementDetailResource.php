<?php

namespace App\Http\Resources\Provider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Detailed financial statement for a single client.
 * $resource is the array returned by ProviderClientService::statement():
 *   ['user' => User, 'summary' => stdClass, 'totalPaid' => float, 'bookings' => LengthAwarePaginator]
 */
class ProviderClientStatementDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user      = $this->resource['user'];
        $summary   = $this->resource['summary'];
        $totalPaid = (float) ($this->resource['totalPaid'] ?? 0);
        $bookings  = $this->resource['bookings'];

        $totalCharged = (float) ($summary->total_charged ?? 0);

        return [
            'client' => [
                'uuid'  => $user->uuid,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'summary' => [
                'total_bookings'     => (int) ($summary->total_bookings    ?? 0),
                'completed_bookings' => (int) ($summary->completed_bookings ?? 0),
                'cancelled_bookings' => (int) ($summary->cancelled_bookings ?? 0),
                'total_charged'      => $totalCharged,
                'total_paid'         => $totalPaid,
                'outstanding'        => round($totalCharged - $totalPaid, 2),
                'last_booking_date'  => $summary->last_booking_date,
            ],
            'bookings' => ProviderBookingResource::collection($bookings->items()),
        ];
    }
}
