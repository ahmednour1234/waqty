<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingActivity extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'booking_id',
        'event',
        'description',
        'actor_type',
        'actor_id',
        'actor_name',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata'   => 'array',
            'created_at' => 'datetime',
        ];
    }

    // Event type constants
    const EVENT_CREATED          = 'created';
    const EVENT_STATUS_CHANGED   = 'status_changed';
    const EVENT_PAYMENT_RECORDED = 'payment_recorded';
    const EVENT_NOTE_ADDED       = 'note_added';

    // Actor type constants
    const ACTOR_PROVIDER = 'provider';
    const ACTOR_EMPLOYEE = 'employee';
    const ACTOR_SYSTEM   = 'system';
    const ACTOR_USER     = 'user';

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
