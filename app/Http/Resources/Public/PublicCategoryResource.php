<?php

namespace App\Http\Resources\Public;

use App\Http\Helpers\LocalizationHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $language = LocalizationHelper::getCurrentLanguage($request);

        return [
            'uuid' => $this->uuid,
            'name' => LocalizationHelper::getLocalizedName($this->name, $language),
            'image_url' => $this->image_path ? route('images.serve', ['type' => 'categories', 'uuid' => $this->uuid]) : null,
            'has_subcategories' => $this->relationLoaded('subcategories') ? $this->subcategories->count() > 0 : false,
            'subcategories_count' => $this->when($this->relationLoaded('subcategories'), function () {
                return $this->subcategories->count();
            }),
            'subcategories' => $this->when($this->relationLoaded('subcategories') && $this->subcategories->count() > 0, function () use ($request) {
                return PublicSubcategoryResource::collection($this->subcategories);
            }),
        ];
    }
}
