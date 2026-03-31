<?php

namespace App\Http\Resources\Provider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderPaymentResource extends JsonResource
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
                'status'       => $this->booking?->status,
            ]),
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}
