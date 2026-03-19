<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Provider extends Authenticatable implements JWTSubject
{
    use Notifiable, SoftDeletes, HasUuid;

    protected $fillable = [
        'name',
        'email',
        'password',
        'code',
        'phone',
        'logo_path',
        'category_id',
        'country_id',
        'city_id',
        'active',
        'blocked',
        'banned',
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
            'banned' => 'boolean',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function branches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProviderBranch::class, 'provider_id');
    }

    public function mainBranch(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProviderBranch::class, 'provider_id')->where('is_main', true);
    }

    public function services(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Service::class, 'provider_id');
    }
}
