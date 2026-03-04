<?php

namespace App\Http\Resources\Public;

use App\Http\Helpers\LocalizationHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicProviderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $language = LocalizationHelper::getCurrentLanguage($request);

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'category' => $this->when($this->relationLoaded('category') && $this->category, function () use ($language) {
                return [
                    'uuid' => $this->category->uuid,
                    'name' => LocalizationHelper::getLocalizedName($this->category->name, $language),
                ];
            }),
            'city' => $this->when($this->relationLoaded('city') && $this->city, function () use ($language) {
                return [
                    'uuid' => $this->city->uuid,
                    'name' => LocalizationHelper::getLocalizedName($this->city->name, $language),
                ];
            }),
            'logo_url' => $this->when($this->logo_path, function () {
                return route('images.serve', ['type' => 'providers', 'uuid' => $this->uuid]);
            }),
            'main_branch' => $this->when($this->relationLoaded('mainBranch') && $this->mainBranch, function () use ($language) {
                return [
                    'uuid' => $this->mainBranch->uuid,
                    'city_name' => LocalizationHelper::getLocalizedName($this->mainBranch->city->name ?? [], $language),
                    'latitude' => $this->mainBranch->latitude,
                    'longitude' => $this->mainBranch->longitude,
                    'logo_url' => $this->mainBranch->logo_path ? route('images.serve', ['type' => 'branches', 'uuid' => $this->mainBranch->uuid]) : null,
                ];
            }),
        ];
    }
}
