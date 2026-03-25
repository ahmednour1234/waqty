<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rating extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid',
        'booking_id',
        'user_id',
        'rating',
        'comment',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
