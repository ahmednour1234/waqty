<?php

namespace App\Http\Resources\Employee;

use App\Http\Resources\BookingRatingResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'           => $this->uuid,
            'status'         => $this->status,
            'payment_status' => $this->payment_status,
            'booking_date'   => $this->booking_date?->toDateString(),
            'start_time'     => $this->start_time,
            'end_time'       => $this->end_time,
            'price'          => $this->price,
            'currency'       => $this->currency,
            'notes'          => $this->notes,
            'session_started_at' => $this->session_started_at?->toIso8601String(),
            'session_ended_at'   => $this->session_ended_at?->toIso8601String(),
            'service'        => $this->service_snapshot,
            'branch'         => $this->branch_snapshot,
            'user'           => $this->whenLoaded('user', fn() => [
                'uuid'  => $this->user?->uuid,
                'name'  => $this->user?->name,
                'phone' => $this->user?->phone,
            ]),
            'rating'         => $this->whenLoaded('rating', fn () => new BookingRatingResource($this->rating)),
            'payment'        => $this->whenLoaded('latestPayment', fn() => $this->latestPayment ? [
                'uuid'           => $this->latestPayment->uuid,
                'payment_method' => $this->latestPayment->payment_method,
                'amount'         => $this->latestPayment->amount,
                'status'         => $this->latestPayment->status,
            ] : null),
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}
