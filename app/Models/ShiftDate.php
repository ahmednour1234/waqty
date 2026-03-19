<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftDate extends Model
{
    use SoftDeletes, HasUuid;

    protected $fillable = [
        'shift_id',
        'shift_date',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'shift_date' => 'date',
            'active'     => 'boolean',
        ];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'shift_date_employees', 'shift_date_id', 'employee_id')
                    ->withPivot(['uuid', 'assigned_at'])
                    ->withTimestamps();
    }

    public function employeeAssignments(): HasMany
    {
        return $this->hasMany(ShiftDateEmployee::class, 'shift_date_id');
    }
}
