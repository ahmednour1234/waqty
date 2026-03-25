<?php

namespace App\Http\Resources\Provider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderEmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
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
        ];
    }
}
