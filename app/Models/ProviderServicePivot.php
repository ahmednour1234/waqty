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
    ];

    protected function casts(): array
    {
        return [
            'active'      => 'boolean',
            'name'        => 'array',
            'description' => 'array',
        ];
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class, 'sub_category_id');
    }
}
