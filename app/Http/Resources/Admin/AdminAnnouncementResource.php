<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminAnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'       => $this->uuid,
            'title_en'   => $this->title_en,
            'title_ar'   => $this->title_ar,
            'message_en' => $this->message_en,
            'message_ar' => $this->message_ar,
            'target'     => $this->target,
            'priority'   => $this->priority,
            'active'     => $this->active,
            'ends_at'    => $this->ends_at?->toDateTimeString(),
            'created_by' => $this->whenLoaded('createdByAdmin', fn() => $this->createdByAdmin ? [
                'uuid' => $this->createdByAdmin->uuid,
                'name' => $this->createdByAdmin->name,
            ] : null),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
