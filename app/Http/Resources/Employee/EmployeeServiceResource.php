<?php

namespace App\Http\Resources\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        $name = $this->name ?? [];
        $description = $this->description ?? [];

        return [
            'uuid'              => $this->uuid,
            'sub_category_uuid' => $this->whenLoaded('subCategory', fn () => $this->subCategory->uuid),
            'sub_category_name' => $this->whenLoaded('subCategory', function () use ($locale) {
                $n = $this->subCategory->name ?? [];
                return $n[$locale] ?? $n['ar'] ?? '';
            }),
            'name'              => $name[$locale] ?? $name['ar'] ?? '',
            'description'       => $description[$locale] ?? $description['ar'] ?? '',
            'image_url'         => $this->image_path
                ? route('images.serve', ['type' => 'services', 'uuid' => $this->uuid])
                : null,
            'active'            => $this->active,
        ];
    }
}
