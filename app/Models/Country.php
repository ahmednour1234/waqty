<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes, HasUuid;

    protected $fillable = [
        'name',
        'iso2',
        'phone_code',
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

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
