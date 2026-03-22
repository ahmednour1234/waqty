<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\ServicePrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes, HasUuid;

    protected $fillable = [
        'sub_category_id',
        'name',
        'description',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'name'        => 'array',
            'description' => 'array',
        ];
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(Provider::class)
            ->using(ProviderServicePivot::class)
            ->withPivot('active', 'deleted_at', 'name', 'description', 'image_path', 'sub_category_id')
            ->withTimestamps();
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class, 'sub_category_id');
    }

    /**
     * Default (non-scoped) active prices — one per provider.
     * These are the fixed prices that apply across all employees of a provider.
     */
    public function defaultPrices(): HasMany
    {
        return $this->hasMany(ServicePrice::class)
            ->whereNull('branch_id')
            ->whereNull('employee_id')
            ->whereNull('pricing_group_id')
            ->where('active', true)
            ->whereNull('deleted_at');
    }
}
