<?php

namespace App\Http\Resources\Provider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderShiftTemplateResource extends JsonResource
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
            'active'      => $this->active,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
