<?php

namespace App\Http\Resources\Public;

use App\Models\Subcategory;
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
            'providers'         => $this->whenLoaded('providers', function () use ($locale) {
                $prices = $this->relationLoaded('defaultPrices')
                    ? $this->defaultPrices->keyBy('provider_id')
                    : collect();

                return $this->providers
                    ->filter(fn ($p) => is_null($p->pivot->deleted_at) && $p->pivot->active)
                    ->map(function ($p) use ($locale, $prices) {
                        $pivotName = $p->pivot->name
                            ? ($p->pivot->name[$locale] ?? $p->pivot->name['ar'] ?? null)
                            : null;
                        $pivotDesc = $p->pivot->description
                            ? ($p->pivot->description[$locale] ?? $p->pivot->description['ar'] ?? null)
                            : null;
                        $pivotImage = $p->pivot->image_path
                            ? route('images.serve', ['type' => 'services', 'uuid' => $this->uuid])
                            : null;
                        $pivotSubCatUuid = $p->pivot->sub_category_id
                            ? Subcategory::find($p->pivot->sub_category_id)?->uuid
                            : null;

                        return [
                            'uuid'                       => $p->uuid,
                            'name'                       => $p->name,
                            'logo_url'                   => $p->logo_path
                                ? route('images.serve', ['type' => 'providers', 'uuid' => $p->uuid])
                                : null,
                            'default_price'              => isset($prices[$p->id])
                                ? (string) $prices[$p->id]->price
                                : null,
                            'estimated_duration_minutes' => $p->pivot->estimated_duration_minutes ?? null,
                            'service_name'               => $pivotName,
                            'service_description'        => $pivotDesc,
                            'service_image_url'          => $pivotImage,
                            'sub_category_uuid'          => $pivotSubCatUuid,
                        ];
                    })
                    ->values()->toArray();
            }),
        ];
    }
}
