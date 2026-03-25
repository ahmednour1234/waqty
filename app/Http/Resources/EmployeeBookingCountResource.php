<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeBookingCountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'uuid' => $this['uuid'],
            'name' => $this['name'],
            'job_title' => $this['job_title'],
            'email' => $this['email'],
            'phone' => $this['phone'],
            'active' => $this['active'],
            'branch_uuid' => $this['branch_uuid'],
            'branch_name' => $this['branch_name'] ?? null,
            'booking_count' => $this['booking_count'],
        ];
    }
}
