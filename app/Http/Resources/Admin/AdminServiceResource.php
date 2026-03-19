<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        $subName = $this->whenLoaded('subCategory', function () use ($locale) {
            $name = $this->subCategory->name ?? [];
            return $name[$locale] ?? $name['ar'] ?? '';
        });

        return [
            'uuid'               => $this->uuid,
            'provider_uuid'      => $this->whenLoaded('provider', fn () => $this->provider->uuid),
            'provider_name'      => $this->whenLoaded('provider', fn () => $this->provider->name),
            'sub_category_uuid'  => $this->whenLoaded('subCategory', fn () => $this->subCategory->uuid),
            'sub_category_name'  => $subName,
            'name'               => $this->name,
            'description'        => $this->description,
            'image_url'          => $this->image_path
                ? route('images.serve', ['type' => 'services', 'uuid' => $this->uuid])
                : null,
            'active'             => $this->active,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
            'deleted_at'         => $this->deleted_at,
        ];
    }
}
