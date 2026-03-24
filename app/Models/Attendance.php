<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasUuid;

    protected $fillable = [
        'employee_id',
        'shift_date_id',
        'check_in_at',
        'check_out_at',
        'notes',
        'working_minutes',
    ];

    protected function casts(): array
    {
        return [
            'check_in_at'  => 'datetime',
            'check_out_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shiftDate(): BelongsTo
    {
        return $this->belongsTo(ShiftDate::class);
    }

    /**
     * Computed status based on whether the record has been checked out.
     */
    public function getStatusAttribute(): string
    {
        return $this->check_out_at ? 'checked_out' : 'checked_in';
    }
}
