<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProviderEmailVerificationRepository
{
    public function invalidatePrevious(int $providerId): void
    {
        DB::table('provider_email_verifications')
            ->where('provider_id', $providerId)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);
    }

    public function createOtp(int $providerId, string $otpHash, Carbon $expiresAt, ?string $ip, ?string $ua): void
    {
        DB::table('provider_email_verifications')->insert([
            'provider_id' => $providerId,
            'otp_hash' => $otpHash,
            'expires_at' => $expiresAt,
            'attempts' => 0,
            'locked_until' => null,
            'created_ip' => $ip,
            'user_agent' => $ua,
            'created_at' => now(),
        ]);
    }

    public function findLatestValidByProvider(int $providerId): ?object
    {
        return DB::table('provider_email_verifications')
            ->where('provider_id', $providerId)
            ->whereNull('used_at')
            ->orderByDesc('id')
            ->first();
    }

    public function incrementAttempts(object $reset): void
    {
        DB::table('provider_email_verifications')
            ->where('id', $reset->id)
            ->update(['attempts' => $reset->attempts + 1]);
    }

    public function markUsed(object $reset): void
    {
        DB::table('provider_email_verifications')
            ->where('id', $reset->id)
            ->update(['used_at' => now()]);
    }

    public function lock(object $reset, Carbon $until): void
    {
        DB::table('provider_email_verifications')
            ->where('id', $reset->id)
            ->update(['locked_until' => $until]);
    }
}
