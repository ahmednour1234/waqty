<?php

namespace App\Repositories\Contracts;

use App\Models\ProviderPasswordReset;

interface ProviderPasswordResetRepositoryInterface
{
    public function createToken(int $providerId, string $tokenHash, \DateTime $expiresAt, ?string $ip, ?string $userAgent): ProviderPasswordReset;

    public function findValidByEmailAndToken(string $email, string $token): ?ProviderPasswordReset;

    public function invalidatePrevious(int $providerId): void;

    public function markUsed(int $id): bool;
}
