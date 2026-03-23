<?php

namespace App\Http\Resources\Employee;

use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Returns a service together with the requesting employee's resolved price.
 *
 * $this->resource = [
 *   'service' => Service model (with optional providers pivot loaded),
 *   'pricing' => [
 *       'final_price'   => float|null,
 *       'source_type'   => 'employee'|'group'|'branch'|'default',
 *       'pricing_group' => ['uuid' => string, 'name' => string]|null,
 *       ...
 *   ]|null
 * ]
 */
class EmployeeServiceWithPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $service = $this->resource['service'];
        $pricing = $this->resource['pricing'];
        $locale  = app()->getLocale();

        // Respect provider-level pivot overrides (same logic as EmployeeServiceResource)
        $pivot = null;
        if ($service->relationLoaded('providers') && $service->providers->isNotEmpty()) {
            $pivot = $service->providers->first()->pivot;
        }

        $name        = ($pivot && $pivot->name)        ? $pivot->name        : ($service->name        ?? []);
        $description = ($pivot && $pivot->description) ? $pivot->description : ($service->description ?? []);
        $imagePath   = ($pivot && $pivot->image_path)  ? $pivot->image_path  : $service->image_path;

        $subCat = null;
        if ($pivot && $pivot->sub_category_id) {
            $subCat = Subcategory::find($pivot->sub_category_id);
        }
        if (!$subCat && $service->relationLoaded('subCategory')) {
            $subCat = $service->subCategory;
        }

        $pricingData = null;
        if ($pricing !== null) {
            $pricingData = [
                'final_price'   => $pricing['final_price'],
                'source_type'   => $pricing['source_type'],
                'pricing_group' => $pricing['pricing_group'] ?? null,
            ];
        }

        return [
            'uuid'              => $service->uuid,
            'sub_category_uuid' => $subCat?->uuid,
            'sub_category_name' => $subCat ? ($subCat->name[$locale] ?? $subCat->name['ar'] ?? '') : null,
            'name'              => $name[$locale]        ?? $name['ar']        ?? '',
            'description'       => $description[$locale] ?? $description['ar'] ?? '',
            'image_url'         => $imagePath
                ? route('images.serve', ['type' => 'services', 'uuid' => $service->uuid])
                : null,
            'active'            => $service->active,
            'estimated_duration_minutes' => $pivot?->estimated_duration_minutes,
            'pricing'           => $pricingData,
        ];
    }
}
