<?php

namespace App\Http\Resources\Admin;

use App\Http\Helpers\LocalizationHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminPricingGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'            => $this->uuid,
            'provider_uuid'   => $this->when($this->relationLoaded('provider') && $this->provider, $this->provider?->uuid),
            'provider_name'   => $this->when($this->relationLoaded('provider') && $this->provider, $this->provider?->name),
            'name'            => $this->name,  // full {ar, en} for admin
            'active'          => $this->active,
            'employees_count' => $this->when(isset($this->employees_count), $this->employees_count),
            'employees'       => $this->when(
                $this->relationLoaded('employees'),
                fn () => $this->employees->map(fn ($emp) => [
                    'uuid'  => $emp->uuid,
                    'name'  => $emp->name,
                    'email' => $emp->email,
                ])
            ),
            'created_at'      => $this->created_at?->toISOString(),
            'updated_at'      => $this->updated_at?->toISOString(),
            'deleted_at'      => $this->deleted_at?->toISOString(),
        ];
    }
}
