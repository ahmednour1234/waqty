<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricingGroup extends Model
{
    use SoftDeletes, HasUuid;

    protected $fillable = [
        'provider_id',
        'name',
        'active',
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

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'pricing_group_employees')
            ->using(PricingGroupEmployee::class)
            ->withTimestamps();
    }

    public function groupEmployees(): HasMany
    {
        return $this->hasMany(PricingGroupEmployee::class);
    }

    public function servicePrices(): HasMany
    {
        return $this->hasMany(ServicePrice::class);
    }
}
