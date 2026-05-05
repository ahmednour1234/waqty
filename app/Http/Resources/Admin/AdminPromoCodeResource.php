<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminPromoCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'         => $this->uuid,
            'code'         => $this->code,
            'type'         => $this->type,
            'value'        => (float) $this->value,
            'min_order'    => (float) $this->min_order,
            'max_discount' => $this->max_discount !== null ? (float) $this->max_discount : null,
            'usage_limit'  => $this->usage_limit,
            'usage_count'  => $this->usage_count,
            'valid_until'  => $this->valid_until?->toDateString(),
            'active'       => $this->active,
            'is_expired'   => $this->isExpired(),
            'is_exhausted' => $this->isExhausted(),
            'created_by'   => $this->whenLoaded('createdByAdmin', fn() => $this->createdByAdmin ? [
                'uuid' => $this->createdByAdmin->uuid,
                'name' => $this->createdByAdmin->name,
            ] : null),
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
            'deleted_at'   => $this->deleted_at,
        ];
    }
}
