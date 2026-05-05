<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminBannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'        => $this->uuid,
            'title'       => $this->title,
            'image_url'   => $this->image_url,
            'placement'   => $this->placement,
            'dimensions'  => $this->dimensions,
            'active'      => $this->active,
            'sort_order'  => $this->sort_order,
            'starts_at'   => $this->starts_at?->toDateString(),
            'ends_at'     => $this->ends_at?->toDateString(),
            'created_by'  => $this->whenLoaded('createdByAdmin', fn() => $this->createdByAdmin ? [
                'uuid' => $this->createdByAdmin->uuid,
                'name' => $this->createdByAdmin->name,
            ] : null),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'deleted_at'  => $this->deleted_at,
        ];
    }
}
