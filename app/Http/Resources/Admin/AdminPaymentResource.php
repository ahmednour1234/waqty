<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'           => $this->uuid,
            'payment_method' => $this->payment_method,
            'amount'         => $this->amount,
            'status'         => $this->status,
            'transaction_id' => $this->transaction_id,
            'notes'          => $this->notes,
            'booking'        => $this->whenLoaded('booking', fn() => [
                'uuid'         => $this->booking?->uuid,
                'booking_date' => $this->booking?->booking_date?->toDateString(),
                'start_time'   => $this->booking?->start_time,
                'end_time'     => $this->booking?->end_time,
                'price'        => $this->booking?->price,
                'status'       => $this->booking?->status,
            ]),
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
            'deleted_at'     => $this->deleted_at?->toIso8601String(),
        ];
    }
}
