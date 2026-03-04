<?php

namespace App\Repositories\Contracts;

use App\Models\EmployeePasswordReset;

interface EmployeePasswordResetRepositoryInterface
{
    public function createOtp(int $employeeId, string $otpHash, \DateTime $expiresAt, ?string $ip, ?string $userAgent): EmployeePasswordReset;

    public function findLatestValid(int $employeeId): ?EmployeePasswordReset;

    public function invalidatePrevious(int $employeeId): void;

    public function incrementAttempts(int $id): void;

    public function lock(int $id, \DateTime $lockedUntil): void;

    public function markUsed(int $id): void;
}
