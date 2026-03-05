<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePasswordReset extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'otp_hash',
        'expires_at',
        'used_at',
        'attempts',
        'locked_until',
        'created_ip',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'locked_until' => 'datetime',
            'attempts' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
