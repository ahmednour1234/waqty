<?php

namespace App\Http\Resources\Public;

use App\Http\Helpers\LocalizationHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicProviderBranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $language = LocalizationHelper::getCurrentLanguage($request);

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'city_name' => $this->whenLoaded('city', function () use ($language) {
                return LocalizationHelper::getLocalizedName($this->city->name, $language);
            }),
            'country_name' => $this->whenLoaded('country', function () use ($language) {
                return LocalizationHelper::getLocalizedName($this->country->name, $language);
            }),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'logo_url' => $this->when($this->logo_path, function () {
                return route('images.serve', ['type' => 'branches', 'uuid' => $this->uuid]);
            }),
            'is_main' => $this->is_main,
        ];
    }
}
