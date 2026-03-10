<?php

namespace App\Services;

use App\Models\Provider;
use App\Notifications\ProviderPasswordResetNotification;
use App\Repositories\Contracts\ProviderPasswordResetRepositoryInterface;
use App\Repositories\Contracts\ProviderRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProviderAuthService
{
    public function __construct(
        private ProviderRepositoryInterface $providerRepository,
        private ProviderPasswordResetRepositoryInterface $passwordResetRepository
    ) {
    }

    public function login(string $email, string $password): array
    {
        $provider = $this->providerRepository->findByEmail($email);

        if (!$provider || !Hash::check($password, $provider->password)) {
            throw new \Exception('api.auth.invalid_credentials', 401);
        }

        if (!$provider->active) {
            throw new \Exception('api.auth.account_inactive', 403);
        }

        if ($provider->blocked) {
            throw new \Exception('api.auth.account_blocked', 403);
        }

        if ($provider->banned) {
            throw new \Exception('api.auth.account_banned', 403);
        }

        $provider->update(['last_login_at' => now()]);

        $token = Auth::guard('provider')->login($provider);
        $ttl = config('jwt.ttl') * 60;

        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttl,
            'provider' => $provider,
        ];
    }

    public function me(): Provider
    {
        return Auth::guard('provider')->user();
    }

    public function logout(): void
    {
        Auth::guard('provider')->logout();
    }

    public function requestOtp(string $email, ?string $ip, ?string $userAgent): void
    {
        $provider = $this->providerRepository->findByEmail($email);

        if (! $provider) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(__('api.auth.invalid_credentials'));
        }

        $this->passwordResetRepository->invalidatePrevious($provider->id);

        $otp = app()->environment('testing') ? '1111' : (string) random_int(100000, 999999);
        $otpHash = Hash::make($otp);
        $expiresAt = now()->addMinutes(10);

        $this->passwordResetRepository->createOtp(
            $provider->id,
            $otpHash,
            $expiresAt,
            $ip,
            $userAgent
        );

        $provider->notify(new ProviderPasswordResetNotification($otp));
    }

    public function resetPassword(string $email, string $otp, string $newPassword): void
    {
        $provider = $this->providerRepository->findByEmail($email);

        if (!$provider) {
            throw new \Exception('api.auth.otp_invalid', 400);
        }

        $reset = $this->passwordResetRepository->findLatestValid($provider->id);

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

        $provider->update(['password' => $newPassword]);
        $this->passwordResetRepository->markUsed($reset->id);
    }

    public function verifyOtp(string $email, string $otp): bool
    {
        $provider = $this->providerRepository->findByEmail($email);

        if (!$provider) {
            return false;
        }

        $reset = $this->passwordResetRepository->findLatestValid($provider->id);

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

    public function register(array $data): array
    {
        $provider = $this->providerRepository->create($data);

        $token = Auth::guard('provider')->login($provider);
        $ttl = config('jwt.ttl') * 60;

        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttl,
            'provider' => $provider,
        ];
    }
}
