<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftDateEmployee extends Model
{
    use HasUuid;

    protected $table = 'shift_date_employees';

    protected $fillable = [
        'shift_date_id',
        'employee_id',
        'assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
        ];
    }

    public function shiftDate(): BelongsTo
    {
        return $this->belongsTo(ShiftDate::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
