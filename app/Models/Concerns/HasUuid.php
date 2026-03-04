<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function scopeWhereUuid($query, ?string $uuid)
    {
        if ($uuid === null) {
            return $query->whereRaw('1 = 0');
        }
        return $query->where('uuid', $uuid);
    }
}
