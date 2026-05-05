<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminContentPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'        => $this->uuid,
            'slug'        => $this->slug,
            'title_en'    => $this->title_en,
            'title_ar'    => $this->title_ar,
            'content_en'  => $this->content_en,
            'content_ar'  => $this->content_ar,
            'active'      => $this->active,
            'updated_by'  => $this->whenLoaded('updatedByAdmin', fn() => $this->updatedByAdmin ? [
                'uuid' => $this->updatedByAdmin->uuid,
                'name' => $this->updatedByAdmin->name,
            ] : null),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
