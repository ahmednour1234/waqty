<?php

namespace App\Http\Resources\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'        => $this->uuid,
            'shift_date'  => $this->shift_date?->format('Y-m-d'),
            'start_time'  => $this->start_time,
            'end_time'    => $this->end_time,
            'break_start' => $this->break_start,
            'break_end'   => $this->break_end,
            'active'      => $this->active,

            'shift' => $this->whenLoaded('shift', fn() => $this->shift ? [
                'uuid'  => $this->shift->uuid,
                'title' => $this->shift->title,
                'notes' => $this->shift->notes,
            ] : null),

            'branch' => $this->whenLoaded('shift', fn() =>
                $this->shift?->branch ? [
                    'uuid' => $this->shift->branch->uuid,
                    'name' => $this->shift->branch->name,
                ] : null
            ),

            'provider' => $this->whenLoaded('shift', fn() =>
                $this->shift?->provider ? [
                    'uuid' => $this->shift->provider->uuid,
                    'name' => $this->shift->provider->name,
                ] : null
            ),
        ];
    }
}
