<?php

namespace App\Repositories\Contracts;

use App\Models\ProviderPasswordReset;

interface ProviderPasswordResetRepositoryInterface
{
    public function createOtp(int $providerId, string $otpHash, \DateTime $expiresAt, ?string $ip, ?string $userAgent): ProviderPasswordReset;

    public function findLatestValid(int $providerId): ?ProviderPasswordReset;

    public function invalidatePrevious(int $providerId): void;

    public function incrementAttempts(int $id): void;

    public function lock(int $id, \DateTime $lockedUntil): void;

    public function markUsed(int $id): void;
}
