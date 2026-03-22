<?php

namespace App\Http\Resources\Employee;

use App\Http\Helpers\LocalizationHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Returns ONLY resolved price data for employee audience.
 * Never exposes internal pricing rules or provider-side configuration.
 */
class EmployeeServiceResolvedPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = LocalizationHelper::getCurrentLanguage($request);

        // $this->resource is the resolved price array from PriceResolverService
        return [
            'service_uuid'  => $this->resource['service_uuid'] ?? null,
            'service_name'  => $this->resource['service_name'] ?? null,
            'final_price'   => $this->resource['final_price'] ?? null,
            'resolved_from' => $this->resource['source_type'] ?? null,
            'branch_uuid'   => $this->resource['branch_uuid'] ?? null,
        ];
    }
}
