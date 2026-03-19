<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminShiftDateResource extends JsonResource
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
            'employees'   => $this->whenLoaded('employees', fn() =>
                $this->employees->map(fn($emp) => [
                    'uuid'  => $emp->uuid,
                    'name'  => $emp->name,
                    'email' => $emp->email,
                ])->values()
            ),
        ];
    }
}
