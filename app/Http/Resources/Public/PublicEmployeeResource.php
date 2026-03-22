<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicEmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'uuid'     => $this->uuid,
            'name'     => $this->name,
            'logo_url' => $this->logo_path
                ? route('images.serve', ['type' => 'employees', 'uuid' => $this->uuid])
                : null,
            'provider' => $this->whenLoaded('provider', fn () => [
                'uuid' => $this->provider->uuid,
                'name' => $this->provider->name,
            ]),
            'branch'   => $this->whenLoaded('branch', fn () => $this->branch ? [
                'uuid' => $this->branch->uuid,
                'name' => $this->branch->name,
            ] : null),
            'services' => $this->whenLoaded('assignedServicePrices', function () use ($locale) {
                return $this->assignedServicePrices
                    ->filter(fn ($sp) => $sp->service !== null)
                    ->map(function ($sp) use ($locale) {
                        $name = $sp->service->name ?? [];
                        return [
                            'uuid'  => $sp->service->uuid,
                            'name'  => $name[$locale] ?? $name['ar'] ?? '',
                            'price' => (string) $sp->price,
                        ];
                    })
                    ->values()
                    ->toArray();
            }),
        ];
    }
}
