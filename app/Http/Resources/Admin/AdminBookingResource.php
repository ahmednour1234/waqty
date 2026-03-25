<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\BookingRatingResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminBookingResource extends JsonResource
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
            'service_snapshot'    => $this->service_snapshot,
            'employee_snapshot'   => $this->employee_snapshot,
            'branch_snapshot'     => $this->branch_snapshot,
            'provider_snapshot'   => $this->provider_snapshot,
            'user'                => $this->whenLoaded('user', fn() => [
                'uuid'  => $this->user?->uuid,
                'name'  => $this->user?->name,
                'email' => $this->user?->email,
                'phone' => $this->user?->phone,
            ]),
            'provider'            => $this->whenLoaded('provider', fn() => [
                'uuid' => $this->provider?->uuid,
                'name' => $this->provider?->name,
            ]),
            'employee'            => $this->whenLoaded('employee', fn() => [
                'uuid' => $this->employee?->uuid,
                'name' => $this->employee?->name,
            ]),
            'branch'              => $this->whenLoaded('branch', fn() => [
                'uuid' => $this->branch?->uuid,
                'name' => $this->branch?->name,
            ]),
            'rating'              => $this->whenLoaded('rating', fn () => new BookingRatingResource($this->rating)),
            'created_at'          => $this->created_at?->toIso8601String(),
            'deleted_at'          => $this->deleted_at?->toIso8601String(),
        ];
    }
}
