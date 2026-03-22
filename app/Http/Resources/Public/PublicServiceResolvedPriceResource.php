<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Returns minimal public-facing resolved price only.
 * Never exposes raw rule source UUIDs or internal pricing structure.
 */
class PublicServiceResolvedPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // $this->resource is the resolved price array from PriceResolverService
        return [
            'service_uuid'  => $this->resource['service_uuid'] ?? null,
            'service_name'  => $this->resource['service_name'] ?? null,
            'final_price'   => $this->resource['final_price'] ?? null,
            'provider_uuid' => $this->resource['provider_uuid'] ?? null,
            'provider_name' => $this->resource['provider_name'] ?? null,
        ];
    }
}
