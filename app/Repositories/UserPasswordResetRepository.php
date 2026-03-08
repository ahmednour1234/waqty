<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserPasswordResetRepository
{
    public function invalidatePrevious(int $userId): void
    {
        DB::table('user_password_resets')
            ->where('user_id', $userId)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);
    }

    public function createOtp(int $userId, string $otpHash, Carbon $expiresAt, ?string $ip, ?string $ua): void
    {
        DB::table('user_password_resets')->insert([
            'user_id' => $userId,
            'otp_hash' => $otpHash,
            'expires_at' => $expiresAt,
            'attempts' => 0,
            'locked_until' => null,
            'created_ip' => $ip,
            'user_agent' => $ua,
            'created_at' => now(),
        ]);
    }

    public function findLatestValidByUser(int $userId): ?object
    {
        return DB::table('user_password_resets')
            ->where('user_id', $userId)
            ->whereNull('used_at')
            ->orderByDesc('id')
            ->first();
    }

    public function incrementAttempts(object $reset): void
    {
        DB::table('user_password_resets')
            ->where('id', $reset->id)
            ->update(['attempts' => $reset->attempts + 1]);
    }

    public function markUsed(object $reset): void
    {
        DB::table('user_password_resets')
            ->where('id', $reset->id)
            ->update(['used_at' => now()]);
    }

    public function lock(object $reset, Carbon $until): void
    {
        DB::table('user_password_resets')
            ->where('id', $reset->id)
            ->update(['locked_until' => $until]);
    }
}
