<?php

namespace App\Http\Resources\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSelfResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'job_title' => $this->job_title,
            'email' => $this->email,
            'phone' => $this->phone,
            'branch' => $this->when($this->relationLoaded('branch') && $this->branch, function () {
                return [
                    'uuid' => $this->branch->uuid,
                    'name' => $this->branch->name,
                ];
            }),
            'logo_url' => $this->when($this->logo_path, function () {
                return route('images.serve', ['type' => 'employees', 'uuid' => $this->uuid]);
            }),
            'active' => $this->active,
            'blocked' => $this->blocked,
        ];
    }
}
