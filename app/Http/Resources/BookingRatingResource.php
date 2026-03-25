<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingRatingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'booking_uuid' => $this->whenLoaded('booking', fn () => $this->booking?->uuid),
            'rating' => $this->rating,
            'comment' => $this->comment,
            'active' => $this->active,
            'user' => $this->whenLoaded('user', fn () => [
                'uuid' => $this->user?->uuid,
                'name' => $this->user?->name,
                'phone' => $this->user?->phone,
            ]),
            'rated_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
