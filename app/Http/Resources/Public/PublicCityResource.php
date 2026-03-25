<?php

namespace App\Http\Resources\Public;

use App\Http\Helpers\LocalizationHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicCityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $language = LocalizationHelper::getCurrentLanguage($request);

        return [
            'uuid'              => $this->uuid,
            'name'              => LocalizationHelper::getLocalizedName($this->name, $language),
            'governorate_uuid'  => $this->whenLoaded('governorate', function () use ($language) {
                return $this->governorate->uuid;
            }),
            'governorate_name'  => $this->whenLoaded('governorate', function () use ($language) {
                return LocalizationHelper::getLocalizedName($this->governorate->name, $language);
            }),
        ];
    }
}
