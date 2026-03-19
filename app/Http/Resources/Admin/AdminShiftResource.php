<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'   => $this->uuid,
            'title'  => $this->title,
            'notes'  => $this->notes,
            'active' => $this->active,

            'created_by_type' => $this->created_by_type,
            'created_by_id'   => $this->created_by_id,

            'provider' => $this->whenLoaded('provider', fn() => $this->provider ? [
                'uuid'  => $this->provider->uuid,
                'name'  => $this->provider->name,
                'email' => $this->provider->email,
            ] : null),

            'branch' => $this->whenLoaded('branch', fn() => $this->branch ? [
                'uuid' => $this->branch->uuid,
                'name' => $this->branch->name,
            ] : null),

            'template' => $this->whenLoaded('template', fn() => $this->template ? [
                'uuid' => $this->template->uuid,
                'name' => $this->template->name,
            ] : null),

            'shift_dates' => AdminShiftDateResource::collection(
                $this->whenLoaded('shiftDates')
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
