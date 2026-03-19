<?php

namespace App\Http\Resources\Provider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'uuid'              => $this->uuid,
            'sub_category_uuid' => $this->whenLoaded('subCategory', fn () => $this->subCategory->uuid),
            'sub_category_name' => $this->whenLoaded('subCategory', function () use ($locale) {
                $name = $this->subCategory->name ?? [];
                return $name[$locale] ?? $name['ar'] ?? '';
            }),
            'name'              => $this->name,
            'description'       => $this->description,
            'image_url'         => $this->image_path
                ? route('images.serve', ['type' => 'services', 'uuid' => $this->uuid])
                : null,
            'active'            => $this->active,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
