<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoCode extends Model
{
    use HasUuid, SoftDeletes;

    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED      = 'fixed';

    const TYPES = [
        self::TYPE_PERCENTAGE,
        self::TYPE_FIXED,
    ];

    protected $fillable = [
        'uuid',
        'code',
        'type',
        'value',
        'min_order',
        'max_discount',
        'usage_limit',
        'usage_count',
        'valid_until',
        'active',
        'created_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'value'        => 'decimal:2',
            'min_order'    => 'decimal:2',
            'max_discount' => 'decimal:2',
            'usage_limit'  => 'integer',
            'usage_count'  => 'integer',
            'valid_until'  => 'date',
            'active'       => 'boolean',
        ];
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function isExpired(): bool
    {
        return $this->valid_until->isPast();
    }

    public function isExhausted(): bool
    {
        return $this->usage_limit !== null && $this->usage_count >= $this->usage_limit;
    }

    public function isUsable(): bool
    {
        return $this->active && !$this->isExpired() && !$this->isExhausted();
    }

    public function scopeValid($query)
    {
        return $query->where('active', true)
            ->where('valid_until', '>=', now()->toDateString())
            ->where(fn($q) => $q->whereNull('usage_limit')->orWhereColumn('usage_count', '<', 'usage_limit'));
    }
}
