<?php

namespace App\Http\Resources\Provider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $label = $this->buildLabel();

        return [
            'uuid'       => $this->uuid,
            'event'      => $this->event,
            'label'      => $label,
            'actor_type' => $this->actor_type,
            'actor_name' => $this->actor_name,
            'metadata'   => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    private function buildLabel(): string
    {
        return match ($this->event) {
            'created'          => __('api.bookings.activity.created'),
            'status_changed'   => __('api.bookings.activity.status_changed', [
                'from' => $this->metadata['from'] ?? '',
                'to'   => $this->metadata['to']   ?? '',
            ]),
            'payment_recorded' => __('api.bookings.activity.payment_recorded'),
            'note_added'       => __('api.bookings.activity.note_added'),
            default            => $this->event,
        };
    }
}
