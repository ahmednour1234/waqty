<?php

namespace App\Http\Resources\Provider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderPricingGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'            => $this->uuid,
            'name'            => $this->name,  // full {ar, en} for provider
            'active'          => $this->active,
            'employees_count' => $this->when(isset($this->employees_count), $this->employees_count),
            'employees'       => $this->when(
                $this->relationLoaded('employees'),
                fn () => $this->employees->map(fn ($emp) => [
                    'uuid'        => $emp->uuid,
                    'name'        => $emp->name,
                    'email'       => $emp->email,
                    'branch_uuid' => $emp->relationLoaded('branch') && $emp->branch ? $emp->branch->uuid : null,
                    'branch_name' => $emp->relationLoaded('branch') && $emp->branch ? $emp->branch->name : null,
                ])
            ),
            'created_at'      => $this->created_at?->toISOString(),
            'updated_at'      => $this->updated_at?->toISOString(),
        ];
    }
}
