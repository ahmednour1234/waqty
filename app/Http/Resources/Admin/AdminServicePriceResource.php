<?php

namespace App\Http\Resources\Admin;

use App\Http\Helpers\LocalizationHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminServicePriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = LocalizationHelper::getCurrentLanguage($request);

        return [
            'uuid'                 => $this->uuid,
            'provider_uuid'        => $this->when($this->relationLoaded('provider') && $this->provider, $this->provider?->uuid),
            'provider_name'        => $this->when($this->relationLoaded('provider') && $this->provider, $this->provider?->name),
            'service_uuid'         => $this->when($this->relationLoaded('service') && $this->service, $this->service?->uuid),
            'service_name'         => $this->when(
                $this->relationLoaded('service') && $this->service,
                fn () => LocalizationHelper::getLocalizedName($this->service->name, $lang)
            ),
            'scope_type'           => $this->scope_type,
            'branch_uuid'          => $this->when($this->branch_id && $this->relationLoaded('branch') && $this->branch, $this->branch?->uuid),
            'branch_name'          => $this->when(
                $this->branch_id && $this->relationLoaded('branch') && $this->branch,
                $this->branch?->name
            ),
            'employee_uuid'        => $this->when($this->employee_id && $this->relationLoaded('employee') && $this->employee, $this->employee?->uuid),
            'employee_name'        => $this->when(
                $this->employee_id && $this->relationLoaded('employee') && $this->employee,
                $this->employee?->name
            ),
            'pricing_group_uuid'   => $this->when($this->pricing_group_id && $this->relationLoaded('pricingGroup') && $this->pricingGroup, $this->pricingGroup?->uuid),
            'pricing_group_name'   => $this->when(
                $this->pricing_group_id && $this->relationLoaded('pricingGroup') && $this->pricingGroup,
                fn () => LocalizationHelper::getLocalizedName($this->pricingGroup->name, $lang)
            ),
            'price'                => $this->price,
            'active'               => $this->active,
            'created_at'           => $this->created_at?->toISOString(),
            'updated_at'           => $this->updated_at?->toISOString(),
            'deleted_at'           => $this->deleted_at?->toISOString(),
        ];
    }
}
