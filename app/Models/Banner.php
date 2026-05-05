<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    use HasUuid, SoftDeletes;

    const PLACEMENT_HOME_TOP    = 'home_top';
    const PLACEMENT_HOME_BOTTOM = 'home_bottom';
    const PLACEMENT_HOME_MIDDLE = 'home_middle';
    const PLACEMENT_CATEGORY    = 'category';
    const PLACEMENT_SIDEBAR     = 'sidebar';

    const PLACEMENTS = [
        self::PLACEMENT_HOME_TOP,
        self::PLACEMENT_HOME_BOTTOM,
        self::PLACEMENT_HOME_MIDDLE,
        self::PLACEMENT_CATEGORY,
        self::PLACEMENT_SIDEBAR,
    ];

    const DIMENSIONS = [
        '1200x400',
        '1200x600',
        '800x400',
        '600x300',
    ];

    protected $fillable = [
        'uuid',
        'title',
        'image_path',
        'placement',
        'dimensions',
        'active',
        'sort_order',
        'starts_at',
        'ends_at',
        'created_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'active'     => 'boolean',
            'sort_order' => 'integer',
            'starts_at'  => 'date',
            'ends_at'    => 'date',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path
            ? Storage::url($this->image_path)
            : null;
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }
}
