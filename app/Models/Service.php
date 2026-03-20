<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
            ->withPivot('active', 'deleted_at')
            ->withTimestamps();
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class, 'sub_category_id');
    }
}
