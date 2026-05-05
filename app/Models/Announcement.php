<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasUuid, SoftDeletes;

    const TARGET_ALL       = 'all';
    const TARGET_USERS     = 'users';
    const TARGET_PROVIDERS = 'providers';
    const TARGET_EMPLOYEES = 'employees';
    const TARGET_BRANCHES  = 'branches';

    const PRIORITY_LOW    = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH   = 'high';
    const PRIORITY_URGENT = 'urgent';

    protected $fillable = [
        'uuid',
        'title_en',
        'title_ar',
        'message_en',
        'message_ar',
        'target',
        'priority',
        'active',
        'ends_at',
        'created_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'active'  => 'boolean',
            'ends_at' => 'datetime',
        ];
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }
}
