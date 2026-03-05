<?php

namespace App\Repositories;

use App\Models\Provider;
use App\Models\ProviderPasswordReset;
use App\Repositories\Contracts\ProviderPasswordResetRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class ProviderPasswordResetRepository implements ProviderPasswordResetRepositoryInterface
{
    public function createOtp(int $providerId, string $otpHash, \DateTime $expiresAt, ?string $ip, ?string $userAgent): ProviderPasswordReset
    {
        return ProviderPasswordReset::create([
            'provider_id' => $providerId,
            'otp_hash' => $otpHash,
            'expires_at' => $expiresAt,
            'created_ip' => $ip,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);
    }

    public function findLatestValid(int $providerId): ?ProviderPasswordReset
    {
        return ProviderPasswordReset::where('provider_id', $providerId)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->where(function ($query) {
                $query->whereNull('locked_until')
                      ->orWhere('locked_until', '<=', now());
            })
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function invalidatePrevious(int $providerId): void
    {
        ProviderPasswordReset::where('provider_id', $providerId)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['used_at' => now()]);
    }

    public function incrementAttempts(int $id): void
    {
        ProviderPasswordReset::where('id', $id)->increment('attempts');
    }

    public function lock(int $id, \DateTime $lockedUntil): void
    {
        ProviderPasswordReset::where('id', $id)->update(['locked_until' => $lockedUntil]);
    }

    public function markUsed(int $id): void
    {
        ProviderPasswordReset::where('id', $id)->update(['used_at' => now()]);
    }
}
