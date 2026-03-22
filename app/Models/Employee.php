<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Employee extends Authenticatable implements JWTSubject
{
    use Notifiable, SoftDeletes, HasUuid;

    protected $fillable = [
        'provider_id',
        'branch_id',
        'name',
        'email',
        'phone',
        'password',
        'logo_path',
        'active',
        'blocked',
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
            'active' => 'boolean',
            'blocked' => 'boolean',
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
