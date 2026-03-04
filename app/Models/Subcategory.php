<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subcategory extends Model
{
    use SoftDeletes, HasUuid;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'image_path',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
