<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasUuid, SoftDeletes;

    const METHOD_CASH   = 'cash';
    const METHOD_PAYMOB = 'paymob';

    const STATUS_PENDING   = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED    = 'failed';
    const STATUS_REFUNDED  = 'refunded';

    const ALL_METHODS = [self::METHOD_CASH, self::METHOD_PAYMOB];
    const ALL_STATUSES = [self::STATUS_PENDING, self::STATUS_COMPLETED, self::STATUS_FAILED, self::STATUS_REFUNDED];

    protected $fillable = [
        'uuid',
        'booking_id',
        'payment_method',
        'amount',
        'status',
        'transaction_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
