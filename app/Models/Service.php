<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes, HasUuid;

    protected $fillable = [
        'provider_id',
        'sub_category_id',
        'name',
        'description',
        'image_path',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'name'        => 'array',
            'description' => 'array',
            'active'      => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class, 'sub_category_id');
    }
}
