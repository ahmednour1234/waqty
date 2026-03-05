<?php

namespace App\Services;

use App\Models\Provider;
use App\Notifications\ProviderPasswordResetNotification;
use App\Repositories\Contracts\ProviderPasswordResetRepositoryInterface;
use App\Repositories\Contracts\ProviderRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

    public function requestReset(string $email, ?string $ip, ?string $userAgent): void
    {
        $provider = $this->providerRepository->findByEmail($email);

        if ($provider) {
            $this->passwordResetRepository->invalidatePrevious($provider->id);

            $token = Str::random(60);
            $tokenHash = Hash::make($token);
            $expiresAt = now()->addMinutes(15);

            $this->passwordResetRepository->createToken(
                $provider->id,
                $tokenHash,
                $expiresAt,
                $ip,
                $userAgent
            );

            $provider->notify(new ProviderPasswordResetNotification($token));
        }
    }

    public function resetPassword(string $email, string $token, string $newPassword): void
    {
        $reset = $this->passwordResetRepository->findValidByEmailAndToken($email, $token);

        if (!$reset) {
            throw new \Exception('api.auth.reset_invalid_or_expired', 400);
        }

        $provider = $reset->provider;
        $provider->update(['password' => $newPassword]);

        $this->passwordResetRepository->markUsed($reset->id);
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
