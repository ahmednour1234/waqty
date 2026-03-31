<?php

namespace App\Http\Resources\Provider;

use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        $pivot = null;
        if ($this->relationLoaded('providers') && $this->providers->isNotEmpty()) {
            $pivot = $this->providers->first()->pivot;
        }

        $name        = ($pivot && $pivot->name) ? $pivot->name : ($this->name ?? []);
        $description = ($pivot && $pivot->description) ? $pivot->description : ($this->description ?? []);
        $imagePath   = ($pivot && $pivot->image_path) ? $pivot->image_path : $this->image_path;

        $subCat = null;
        if ($pivot && $pivot->sub_category_id) {
            $subCat = Subcategory::find($pivot->sub_category_id);
        }
        if (!$subCat && $this->relationLoaded('subCategory')) {
            $subCat = $this->subCategory;
        }

        return [
            'uuid'              => $this->uuid,
            'sub_category_uuid' => $subCat?->uuid,
            'sub_category_name' => $subCat ? ($subCat->name[$locale] ?? $subCat->name['ar'] ?? '') : null,
            'category'          => $subCat ? ($subCat->name[$locale] ?? $subCat->name['ar'] ?? '') : null,
            'name'              => $name[$locale] ?? $name['ar'] ?? '',
            'description'       => $description[$locale] ?? $description['ar'] ?? '',
            'image_url'         => $imagePath
                ? route('images.serve', ['type' => 'services', 'uuid' => $this->uuid])
                : null,
            'active'            => $pivot
                ? (bool) $pivot->active
                : $this->whenPivotLoaded('provider_service', fn () => (bool) $this->pivot->active),
            'estimated_duration_minutes' => $pivot?->estimated_duration_minutes,
            'tax_enabled'                => $pivot ? (bool) $pivot->tax_enabled : false,
            'tax_percentage'             => $pivot?->tax_percentage,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
