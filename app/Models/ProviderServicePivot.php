<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProviderServicePivot extends Pivot
{
    use SoftDeletes;

    public $incrementing = false;

    protected $table = 'provider_service';

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
