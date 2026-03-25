<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminEmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'provider_uuid' => $this->when($this->relationLoaded('provider') && $this->provider, $this->provider->uuid),
            'branch_uuid' => $this->when($this->relationLoaded('branch') && $this->branch, $this->branch->uuid),
            'name' => $this->name,
            'job_title' => $this->job_title,
            'email' => $this->email,
            'phone' => $this->phone,
            'active' => $this->active,
            'blocked' => $this->blocked,
            'logo_url' => $this->when($this->logo_path, function () {
                return route('images.serve', ['type' => 'employees', 'uuid' => $this->uuid]);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
