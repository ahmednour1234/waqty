<?php

namespace App\Http\Resources\Provider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'       => $this->uuid,
            'title'      => $this->title,
            'notes'      => $this->notes,
            'active'     => $this->active,

            'template' => $this->whenLoaded('template', fn() => $this->template ? [
                'uuid' => $this->template->uuid,
                'name' => $this->template->name,
            ] : null),

            'branch' => $this->whenLoaded('branch', fn() => $this->branch ? [
                'uuid' => $this->branch->uuid,
                'name' => $this->branch->name,
            ] : null),

            'shift_dates' => ProviderShiftDateResource::collection(
                $this->whenLoaded('shiftDates')
            ),

            'shift_dates_count' => $this->when(
                !$this->relationLoaded('shiftDates'),
                fn() => $this->shiftDates()->count()
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
