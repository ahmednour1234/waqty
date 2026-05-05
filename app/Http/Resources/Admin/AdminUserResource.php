<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'              => $this->uuid,
            'name'              => $this->name,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'gender'            => $this->gender,
            'date_birth'        => $this->date_birth?->toDateString(),
            'active'            => $this->active,
            'blocked'           => $this->blocked,
            'banned'            => $this->banned,
            'email_verified_at' => $this->email_verified_at,
            'avatar_url'        => $this->when($this->image_path, function () {
                return route('images.serve', ['type' => 'users', 'uuid' => $this->uuid]);
            }),
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'deleted_at'        => $this->deleted_at,
        ];
    }
}
