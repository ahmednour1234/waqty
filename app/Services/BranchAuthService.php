<?php

namespace App\Services;

use App\Models\ProviderBranch;
use App\Notifications\BranchPasswordResetNotification;
use App\Repositories\Contracts\BranchPasswordResetRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class BranchAuthService
{
    public function __construct(
        private BranchPasswordResetRepositoryInterface $passwordResetRepository
    ) {
    }

    public function login(string $email, string $password): array
    {
        $branch = ProviderBranch::where('email', $email)->first();

        if (!$branch || !Hash::check($password, $branch->password)) {
            throw new \Exception('api.auth.invalid_credentials', 401);
        }

        if ($branch->blocked) {
            throw new \Exception('api.auth.account_blocked', 403);
        }

        if ($branch->banned) {
            throw new \Exception('api.auth.account_banned', 403);
        }

        if (!$branch->active) {
            throw new \Exception('api.auth.account_inactive', 403);
        }

        $token = Auth::guard('branch')->login($branch);
        $ttl = config('jwt.ttl') * 60;

        return [
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttl,
            'branch'     => $branch,
        ];
    }

    public function logout(): void
    {
        Auth::guard('branch')->logout();
    }

    public function me(): ProviderBranch
    {
        return Auth::guard('branch')->user();
    }

    public function requestOtp(string $email, ?string $ip, ?string $userAgent): void
    {
        $branch = ProviderBranch::where('email', $email)->first();

        if (!$branch) {
            // Do not reveal whether the email exists (prevent enumeration)
            return;
        }

        $this->passwordResetRepository->invalidatePrevious($branch->id);

        $otp = app()->environment('testing') ? '111111' : (string) random_int(100000, 999999);
        $otpHash = Hash::make($otp);
        $expiresAt = now()->addMinutes(10);

        $this->passwordResetRepository->createOtp(
            $branch->id,
            $otpHash,
            $expiresAt,
            $ip,
            $userAgent
        );

        $branch->notify(new BranchPasswordResetNotification($otp));
    }

    public function verifyOtp(string $email, string $otp): bool
    {
        $branch = ProviderBranch::where('email', $email)->first();

        if (!$branch) {
            return false;
        }

        $reset = $this->passwordResetRepository->findLatestValid($branch->id);

        if (!$reset) {
            return false;
        }

        if ($reset->locked_until && $reset->locked_until > now()) {
            return false;
        }

        if (!Hash::check($otp, $reset->otp_hash)) {
            $this->passwordResetRepository->incrementAttempts($reset->id);
            $reset->refresh();

            if ($reset->attempts >= 5) {
                $this->passwordResetRepository->lock($reset->id, now()->addMinutes(15));
            }

            return false;
        }

        return true;
    }

    public function resetPassword(string $email, string $otp, string $newPassword): void
    {
        $branch = ProviderBranch::where('email', $email)->first();

        if (!$branch) {
            throw new \Exception('api.auth.otp_invalid', 400);
        }

        $reset = $this->passwordResetRepository->findLatestValid($branch->id);

        if (!$reset) {
            throw new \Exception('api.auth.otp_invalid', 400);
        }

        if ($reset->locked_until && $reset->locked_until > now()) {
            throw new \Exception('api.auth.otp_locked', 403);
        }

        if (!Hash::check($otp, $reset->otp_hash)) {
            $this->passwordResetRepository->incrementAttempts($reset->id);
            $reset->refresh();

            if ($reset->attempts >= 5) {
                $this->passwordResetRepository->lock($reset->id, now()->addMinutes(15));
                throw new \Exception('api.auth.otp_locked', 403);
            }

            throw new \Exception('api.auth.otp_invalid', 400);
        }

        $branch->update(['password' => $newPassword]);
        $this->passwordResetRepository->markUsed($reset->id);
    }
}
