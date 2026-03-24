<?php

namespace App\Http\Resources\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeAttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'            => $this->uuid,
            'check_in_at'     => $this->check_in_at?->toIso8601String(),
            'check_out_at'    => $this->check_out_at?->toIso8601String(),
            'working_minutes' => $this->working_minutes,
            'status'          => $this->status,
            'notes'           => $this->notes,
            'shift_date'      => $this->when(
                $this->relationLoaded('shiftDate') && $this->shiftDate,
                fn() => [
                    'uuid'       => $this->shiftDate->uuid,
                    'shift_date' => $this->shiftDate->shift_date?->format('Y-m-d'),
                    'start_time' => $this->shiftDate->start_time,
                    'end_time'   => $this->shiftDate->end_time,
                ]
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
