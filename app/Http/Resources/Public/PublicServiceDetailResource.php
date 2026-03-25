<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Contextual detail resource for a single service.
 *
 * The underlying resource is an associative array returned by
 * PublicServiceService::showDetail().
 */
class PublicServiceDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return array_filter([
            'uuid'              => $data['uuid'],
            'name'              => $data['name'],
            'description'       => $data['description'],
            'image_url'         => $data['image_url'] ?? null,
            'sub_category_uuid' => $data['sub_category_uuid'] ?? null,
            'sub_category_name' => $data['sub_category_name'] ?? null,
            'category'          => is_array($data['sub_category_name'] ?? null)
                ? ((app()->getLocale() === 'en')
                    ? (($data['sub_category_name']['en'] ?? $data['sub_category_name']['ar'] ?? null))
                    : (($data['sub_category_name']['ar'] ?? $data['sub_category_name']['en'] ?? null)))
                : ($data['sub_category_name'] ?? null),

            // Present when no provider_uuid was given — all active providers
            'providers'   => $data['providers'] ?? null,

            // Present when provider_uuid is given
            'provider'    => $data['provider'] ?? null,
            'default_price' => $data['default_price'] ?? null,
            'branches'    => $data['branches'] ?? null,

            // Present when both provider_uuid and branch_uuid are given
            'selected_branch' => $data['selected_branch'] ?? null,
            'employees'       => $data['employees'] ?? null,
            'effective_price' => $data['effective_price'] ?? null,
        ], fn ($v) => $v !== null);
    }
}
