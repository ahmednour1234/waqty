<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'                => $this->uuid,
            'status'              => $this->status,
            'payment_status'      => $this->payment_status,
            'booking_date'        => $this->booking_date?->toDateString(),
            'start_time'          => $this->start_time,
            'end_time'            => $this->end_time,
            'price'               => $this->price,
            'currency'            => $this->currency,
            'notes'               => $this->notes,
            'cancellation_reason' => $this->cancellation_reason,
            'cancelled_at'        => $this->cancelled_at?->toIso8601String(),
            'can_cancel'          => $this->can_cancel,
            'service'             => $this->service_snapshot,
            'employee'            => $this->employee_snapshot,
            'branch'              => $this->branch_snapshot,
            'provider'            => $this->provider_snapshot,
            'created_at'          => $this->created_at?->toIso8601String(),
        ];
    }
}
