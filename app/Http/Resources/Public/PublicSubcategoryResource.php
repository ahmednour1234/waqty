<?php

namespace App\Http\Resources\Public;

use App\Http\Helpers\LocalizationHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicSubcategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $language = LocalizationHelper::getCurrentLanguage($request);

        return [
            'uuid' => $this->uuid,
            'name' => LocalizationHelper::getLocalizedName($this->name, $language),
            'image_url' => $this->image_path ? route('images.serve', ['type' => 'subcategories', 'uuid' => $this->uuid]) : null,
        ];
    }
}
