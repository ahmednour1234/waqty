<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PricingGroupEmployee extends Pivot
{
    use HasUuid;

    protected $table = 'pricing_group_employees';

    public $incrementing = true;

    protected $fillable = [
        'pricing_group_id',
        'employee_id',
    ];

    public function pricingGroup(): BelongsTo
    {
        return $this->belongsTo(PricingGroup::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
