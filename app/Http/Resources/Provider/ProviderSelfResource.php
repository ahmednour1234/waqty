<?php

namespace App\Http\Resources\Provider;

use App\Http\Helpers\LocalizationHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderSelfResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $language = LocalizationHelper::getCurrentLanguage($request);

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'code' => $this->code,
            'category' => $this->when($this->relationLoaded('category') && $this->category, function () use ($language) {
                return [
                    'uuid' => $this->category->uuid,
                    'name' => LocalizationHelper::getLocalizedName($this->category->name, $language),
                ];
            }),
            'country' => $this->when($this->relationLoaded('country') && $this->country, function () use ($language) {
                return [
                    'uuid' => $this->country->uuid,
                    'name' => LocalizationHelper::getLocalizedName($this->country->name, $language),
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
            'active' => $this->active,
            'blocked' => $this->blocked,
            'banned' => $this->banned,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
