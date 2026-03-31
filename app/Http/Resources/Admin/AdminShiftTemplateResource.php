<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminShiftTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'        => $this->uuid,
            'name'        => $this->name,
            'start_time'  => $this->start_time,
            'end_time'    => $this->end_time,
            'break_start' => $this->break_start,
            'break_end'   => $this->break_end,
            'color'       => $this->color,
            'active'      => $this->active,

            'provider' => $this->whenLoaded('provider', fn() => $this->provider ? [
                'uuid'  => $this->provider->uuid,
                'name'  => $this->provider->name,
                'email' => $this->provider->email,
            ] : null),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
