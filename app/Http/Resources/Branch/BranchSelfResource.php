<?php

namespace App\Http\Resources\Branch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchSelfResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'     => $this->uuid,
            'name'     => $this->name,
            'email'    => $this->email,
            'phone'    => $this->phone,
            'is_main'  => $this->is_main,
            'active'   => $this->active,
            'blocked'  => $this->blocked,
            'logo_url' => $this->when($this->logo_path, function () {
                return route('images.serve', ['type' => 'branches', 'uuid' => $this->uuid]);
            }),
            'provider' => $this->when($this->relationLoaded('provider') && $this->provider, function () {
                return [
                    'uuid' => $this->provider->uuid,
                    'name' => $this->provider->name,
                ];
            }),
        ];
    }
}
