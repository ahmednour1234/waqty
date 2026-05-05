<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminRatingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'       => $this->uuid,
            'rating'     => $this->rating,
            'comment'    => $this->comment,
            'active'     => $this->active,
            'user'       => $this->whenLoaded('user', fn() => [
                'uuid'  => $this->user?->uuid,
                'name'  => $this->user?->name,
                'email' => $this->user?->email,
                'phone' => $this->user?->phone,
            ]),
            'booking'    => $this->whenLoaded('booking', fn() => [
                'uuid'         => $this->booking?->uuid,
                'booking_date' => $this->booking?->booking_date?->toDateString(),
                'provider'     => $this->when(
                    $this->booking?->relationLoaded('provider'),
                    fn() => [
                        'uuid' => $this->booking?->provider?->uuid,
                        'name' => $this->booking?->provider?->name,
                    ]
                ),
                'branch'       => $this->when(
                    $this->booking?->relationLoaded('branch'),
                    fn() => [
                        'uuid' => $this->booking?->branch?->uuid,
                        'name' => $this->booking?->branch?->name,
                    ]
                ),
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
