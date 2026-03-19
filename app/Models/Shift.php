<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use SoftDeletes, HasUuid;

    protected $fillable = [
        'provider_id',
        'branch_id',
        'shift_template_id',
        'title',
        'notes',
        'created_by_type',
        'created_by_id',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ProviderBranch::class, 'branch_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ShiftTemplate::class, 'shift_template_id');
    }

    public function shiftDates(): HasMany
    {
        return $this->hasMany(ShiftDate::class);
    }
}
