<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProviderServicePivot extends Pivot
{
    use SoftDeletes;

    public $incrementing = false;

    protected $table = 'provider_service';

    protected $fillable = [
        'active',
        'name',
        'description',
        'image_path',
        'sub_category_id',
        'estimated_duration_minutes',
        'tax_enabled',
        'tax_percentage',
    ];

    protected function casts(): array
    {
        return [
            'active'         => 'boolean',
            'name'           => 'array',
            'description'    => 'array',
            'tax_enabled'    => 'boolean',
            'tax_percentage' => 'decimal:2',
        ];
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class, 'sub_category_id');
    }
}
