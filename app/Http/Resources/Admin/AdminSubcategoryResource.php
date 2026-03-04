<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminSubcategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'category_id' => $this->category_id,
            'category_uuid' => $this->whenLoaded('category', function () {
                return $this->category->uuid;
            }),
            'name' => $this->name,
            'slug' => $this->slug,
            'image_url' => $this->image_path ? route('images.serve', ['type' => 'subcategories', 'uuid' => $this->uuid]) : null,
            'active' => $this->active,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
