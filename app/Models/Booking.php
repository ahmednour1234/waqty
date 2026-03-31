<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasUuid, SoftDeletes;

    // Status constants
    const STATUS_PENDING   = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW   = 'no_show';

    const ALL_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
        self::STATUS_NO_SHOW,
    ];

    /**
     * Statuses that occupy a slot (block availability).
     */
    const BLOCKING_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
    ];

    // Payment status constants
    const PAYMENT_STATUS_UNPAID = 'unpaid';
    const PAYMENT_STATUS_PAID   = 'paid';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'uuid',
        'user_id',
        'provider_id',
        'branch_id',
        'employee_id',
        'service_id',
        'booking_date',
        'start_time',
        'end_time',
        'price',
        'currency',
        'status',
        'payment_status',
        'notes',
        'cancellation_reason',
        'cancelled_at',
        'session_started_at',
        'session_ended_at',
        'service_snapshot',
        'employee_snapshot',
        'branch_snapshot',
        'provider_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'booking_date'      => 'date',
            'start_time'        => 'string',
            'end_time'          => 'string',
            'price'             => 'decimal:2',
            'status'            => 'string',
            'payment_status'    => 'string',
            'cancelled_at'      => 'datetime',
            'session_started_at' => 'datetime',
            'session_ended_at'   => 'datetime',
            'service_snapshot'  => 'array',
            'employee_snapshot' => 'array',
            'branch_snapshot'   => 'array',
            'provider_snapshot' => 'array',
        ];
    }

    /**
     * Whether the booking can be cancelled by the user.
     * True when status is pending/confirmed AND booking_date is in the future.
     */
    public function getCanCancelAttribute(): bool
    {
        return in_array($this->status, self::BLOCKING_STATUSES)
            && $this->booking_date->isAfter(today());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ProviderBranch::class, 'branch_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function rating(): HasOne
    {
        return $this->hasOne(Rating::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }
}
