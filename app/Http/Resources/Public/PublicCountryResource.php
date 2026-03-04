<?php

namespace App\Http\Resources\Public;

use App\Http\Helpers\LocalizationHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicCountryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $language = LocalizationHelper::getCurrentLanguage($request);

        return [
            'uuid' => $this->uuid,
            'name' => LocalizationHelper::getLocalizedName($this->name, $language),
            'cities_count' => $this->when($this->relationLoaded('cities'), function () {
                return $this->cities->count();
            }),
            'cities' => $this->when($this->relationLoaded('cities') && $this->cities->count() > 0, function () use ($request) {
                return PublicCityResource::collection($this->cities);
            }),
        ];
    }
}
