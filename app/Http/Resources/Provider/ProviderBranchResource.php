<?php

namespace App\Http\Resources\Provider;

use App\Http\Helpers\LocalizationHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderBranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $language = LocalizationHelper::getCurrentLanguage($request);

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'phone' => $this->phone,
            'country_id' => $this->country_id,
            'country_name' => $this->whenLoaded('country', function () use ($language) {
                return LocalizationHelper::getLocalizedName($this->country->name, $language);
            }),
            'city_id' => $this->city_id,
            'city_name' => $this->whenLoaded('city', function () use ($language) {
                return LocalizationHelper::getLocalizedName($this->city->name, $language);
            }),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'logo_url' => $this->when($this->logo_path, function () {
                return route('images.serve', ['type' => 'branches', 'uuid' => $this->uuid]);
            }),
            'is_main' => $this->is_main,
            'active' => $this->active,
            'blocked' => $this->blocked,
            'banned' => $this->banned,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
