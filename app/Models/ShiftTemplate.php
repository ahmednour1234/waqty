<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftTemplate extends Model
{
    use SoftDeletes, HasUuid;

    protected $fillable = [
        'provider_id',
        'name',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'active',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'name'   => 'array',
            'active' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }
}
