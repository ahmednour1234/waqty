<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeEmailVerificationRepository
{
    public function invalidatePrevious(int $employeeId): void
    {
        DB::table('employee_email_verifications')
            ->where('employee_id', $employeeId)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);
    }

    public function createOtp(int $employeeId, string $otpHash, Carbon $expiresAt, ?string $ip, ?string $ua): void
    {
        DB::table('employee_email_verifications')->insert([
            'employee_id' => $employeeId,
            'otp_hash' => $otpHash,
            'expires_at' => $expiresAt,
            'attempts' => 0,
            'locked_until' => null,
            'created_ip' => $ip,
            'user_agent' => $ua,
            'created_at' => now(),
        ]);
    }

    public function findLatestValidByEmployee(int $employeeId): ?object
    {
        return DB::table('employee_email_verifications')
            ->where('employee_id', $employeeId)
            ->whereNull('used_at')
            ->orderByDesc('id')
            ->first();
    }

    public function incrementAttempts(object $reset): void
    {
        DB::table('employee_email_verifications')
            ->where('id', $reset->id)
            ->update(['attempts' => $reset->attempts + 1]);
    }

    public function markUsed(object $reset): void
    {
        DB::table('employee_email_verifications')
            ->where('id', $reset->id)
            ->update(['used_at' => now()]);
    }

    public function lock(object $reset, Carbon $until): void
    {
        DB::table('employee_email_verifications')
            ->where('id', $reset->id)
            ->update(['locked_until' => $until]);
    }
}
