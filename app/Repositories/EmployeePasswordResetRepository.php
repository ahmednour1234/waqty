<?php

namespace App\Repositories;

use App\Models\EmployeePasswordReset;
use App\Repositories\Contracts\EmployeePasswordResetRepositoryInterface;

class EmployeePasswordResetRepository implements EmployeePasswordResetRepositoryInterface
{
    public function createOtp(int $employeeId, string $otpHash, \DateTime $expiresAt, ?string $ip, ?string $userAgent): EmployeePasswordReset
    {
        return EmployeePasswordReset::create([
            'employee_id' => $employeeId,
            'otp_hash' => $otpHash,
            'expires_at' => $expiresAt,
            'created_ip' => $ip,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);
    }

    public function findLatestValid(int $employeeId): ?EmployeePasswordReset
    {
        return EmployeePasswordReset::where('employee_id', $employeeId)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->where(function ($query) {
                $query->whereNull('locked_until')
                      ->orWhere('locked_until', '<=', now());
            })
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function invalidatePrevious(int $employeeId): void
    {
        EmployeePasswordReset::where('employee_id', $employeeId)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['used_at' => now()]);
    }

    public function incrementAttempts(int $id): void
    {
        EmployeePasswordReset::where('id', $id)->increment('attempts');
    }

    public function lock(int $id, \DateTime $lockedUntil): void
    {
        EmployeePasswordReset::where('id', $id)->update(['locked_until' => $lockedUntil]);
    }

    public function markUsed(int $id): void
    {
        EmployeePasswordReset::where('id', $id)->update(['used_at' => now()]);
    }
}
