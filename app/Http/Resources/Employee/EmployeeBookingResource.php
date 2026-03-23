<?php

namespace App\Http\Resources\Employee;

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
            'service'        => $this->service_snapshot,
            'branch'         => $this->branch_snapshot,
            'user'           => $this->whenLoaded('user', fn() => [
                'uuid'  => $this->user?->uuid,
                'name'  => $this->user?->name,
                'phone' => $this->user?->phone,
            ]),
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}
