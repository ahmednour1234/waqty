<?php

namespace App\Repositories;

use App\Models\ProviderBranchPasswordReset;
use App\Repositories\Contracts\BranchPasswordResetRepositoryInterface;

class BranchPasswordResetRepository implements BranchPasswordResetRepositoryInterface
{
    public function createOtp(int $branchId, string $otpHash, \DateTime $expiresAt, ?string $ip, ?string $userAgent): ProviderBranchPasswordReset
    {
        return ProviderBranchPasswordReset::create([
            'branch_id'  => $branchId,
            'otp_hash'   => $otpHash,
            'expires_at' => $expiresAt,
            'created_ip' => $ip,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);
    }

    public function findLatestValid(int $branchId): ?ProviderBranchPasswordReset
    {
        return ProviderBranchPasswordReset::where('branch_id', $branchId)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->where(function ($query) {
                $query->whereNull('locked_until')
                      ->orWhere('locked_until', '<=', now());
            })
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function invalidatePrevious(int $branchId): void
    {
        ProviderBranchPasswordReset::where('branch_id', $branchId)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['used_at' => now()]);
    }

    public function incrementAttempts(int $id): void
    {
        ProviderBranchPasswordReset::where('id', $id)->increment('attempts');
    }

    public function lock(int $id, \DateTime $lockedUntil): void
    {
        ProviderBranchPasswordReset::where('id', $id)->update(['locked_until' => $lockedUntil]);
    }

    public function markUsed(int $id): void
    {
        ProviderBranchPasswordReset::where('id', $id)->update(['used_at' => now()]);
    }
}
