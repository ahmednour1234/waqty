<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicePrice extends Model
{
    use SoftDeletes, HasUuid;

    protected $fillable = [
        'provider_id',
        'service_id',
        'branch_id',
        'employee_id',
        'pricing_group_id',
        'price',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'price'  => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    /**
     * Returns one of: default | branch | employee | group
     */
    public function getScopeTypeAttribute(): string
    {
        if ($this->employee_id !== null) {
            return 'employee';
        }
        if ($this->pricing_group_id !== null) {
            return 'group';
        }
        if ($this->branch_id !== null) {
            return 'branch';
        }
        return 'default';
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ProviderBranch::class, 'branch_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function pricingGroup(): BelongsTo
    {
        return $this->belongsTo(PricingGroup::class);
    }
}
