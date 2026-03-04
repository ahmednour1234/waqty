<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProviderBranch extends Model
{
    use SoftDeletes, HasUuid;

    protected $fillable = [
        'provider_id',
        'name',
        'phone',
        'country_id',
        'city_id',
        'latitude',
        'longitude',
        'logo_path',
        'is_main',
        'active',
        'blocked',
        'banned',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'blocked' => 'boolean',
            'banned' => 'boolean',
            'is_main' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
