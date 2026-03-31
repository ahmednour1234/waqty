<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Employee extends Authenticatable implements JWTSubject
{
    use Notifiable, SoftDeletes, HasUuid;

    const AVAILABILITY_AVAILABLE  = 'available';
    const AVAILABILITY_IN_SESSION = 'in_session';
    const AVAILABILITY_BREAK      = 'break';
    const AVAILABILITY_OFF        = 'off';

    const AVAILABILITY_STATUSES = [
        self::AVAILABILITY_AVAILABLE,
        self::AVAILABILITY_IN_SESSION,
        self::AVAILABILITY_BREAK,
        self::AVAILABILITY_OFF,
    ];

    /** Statuses the employee can manually set (in_session is set only via session start/end) */
    const MANUAL_AVAILABILITY_STATUSES = [
        self::AVAILABILITY_AVAILABLE,
        self::AVAILABILITY_BREAK,
        self::AVAILABILITY_OFF,
    ];

    protected $fillable = [
        'provider_id',
        'branch_id',
        'name',
        'job_title',
        'email',
        'phone',
        'password',
        'logo_path',
        'salary',
        'commission_percentage',
        'active',
        'blocked',
        'has_app_access',
        'availability_status',
        'availability_updated_at',
        'last_login_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'salary' => 'decimal:2',
            'commission_percentage' => 'decimal:2',
            'active' => 'boolean',
            'blocked' => 'boolean',
            'has_app_access' => 'boolean',
            'availability_updated_at' => 'datetime',
            'last_login_at' => 'datetime',
            'email_verified_at' => 'datetime',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ProviderBranch::class, 'branch_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Service prices assigned directly to this employee (employee-scoped prices).
     */
    public function assignedServicePrices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ServicePrice::class)
            ->whereNotNull('employee_id')
            ->where('active', true)
            ->whereNull('deleted_at')
            ->with('service:id,uuid,name');
    }
}
