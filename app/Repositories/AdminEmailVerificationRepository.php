<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminEmailVerificationRepository
{
    public function invalidatePrevious(int $adminId): void
    {
        DB::table('admin_email_verifications')
            ->where('admin_id', $adminId)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);
    }

    public function createOtp(int $adminId, string $otpHash, Carbon $expiresAt, ?string $ip, ?string $ua): void
    {
        DB::table('admin_email_verifications')->insert([
            'admin_id' => $adminId,
            'otp_hash' => $otpHash,
            'expires_at' => $expiresAt,
            'attempts' => 0,
            'locked_until' => null,
            'created_ip' => $ip,
            'user_agent' => $ua,
            'created_at' => now(),
        ]);
    }

    public function findLatestValidByAdmin(int $adminId): ?object
    {
        return DB::table('admin_email_verifications')
            ->where('admin_id', $adminId)
            ->whereNull('used_at')
            ->orderByDesc('id')
            ->first();
    }

    public function incrementAttempts(object $reset): void
    {
        DB::table('admin_email_verifications')
            ->where('id', $reset->id)
            ->update(['attempts' => $reset->attempts + 1]);
    }

    public function markUsed(object $reset): void
    {
        DB::table('admin_email_verifications')
            ->where('id', $reset->id)
            ->update(['used_at' => now()]);
    }

    public function lock(object $reset, Carbon $until): void
    {
        DB::table('admin_email_verifications')
            ->where('id', $reset->id)
            ->update(['locked_until' => $until]);
    }
}
