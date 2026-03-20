<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        $name = $this->name ?? [];
        $description = $this->description ?? [];

        return [
            'uuid'              => $this->uuid,
            'name'              => $name[$locale] ?? $name['ar'] ?? '',
            'description'       => $description[$locale] ?? $description['ar'] ?? '',
            'image_url'         => $this->image_path
                ? route('images.serve', ['type' => 'services', 'uuid' => $this->uuid])
                : null,
            'sub_category_uuid' => $this->whenLoaded('subCategory', fn () => $this->subCategory->uuid),
            'sub_category_name' => $this->whenLoaded('subCategory', function () use ($locale) {
                $n = $this->subCategory->name ?? [];
                return $n[$locale] ?? $n['ar'] ?? '';
            }),
            'providers'         => $this->whenLoaded('providers', fn () =>
                $this->providers
                    ->filter(fn ($p) => is_null($p->pivot->deleted_at) && $p->pivot->active)
                    ->map(fn ($p) => ['uuid' => $p->uuid, 'name' => $p->name])
                    ->values()->toArray()
            ),
        ];
    }
}
