<?php

namespace App\Repositories\Contracts;

use App\Models\ProviderBranchPasswordReset;

interface BranchPasswordResetRepositoryInterface
{
    public function createOtp(int $branchId, string $otpHash, \DateTime $expiresAt, ?string $ip, ?string $userAgent): ProviderBranchPasswordReset;

    public function findLatestValid(int $branchId): ?ProviderBranchPasswordReset;

    public function invalidatePrevious(int $branchId): void;

    public function incrementAttempts(int $id): void;

    public function lock(int $id, \DateTime $lockedUntil): void;

    public function markUsed(int $id): void;
}
