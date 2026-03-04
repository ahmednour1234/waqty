<?php

namespace App\Repositories;

use App\Models\Provider;
use App\Models\ProviderPasswordReset;
use App\Repositories\Contracts\ProviderPasswordResetRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class ProviderPasswordResetRepository implements ProviderPasswordResetRepositoryInterface
{
    public function createToken(int $providerId, string $tokenHash, \DateTime $expiresAt, ?string $ip, ?string $userAgent): ProviderPasswordReset
    {
        return ProviderPasswordReset::create([
            'provider_id' => $providerId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'created_ip' => $ip,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);
    }

    public function findValidByEmailAndToken(string $email, string $token): ?ProviderPasswordReset
    {
        $provider = Provider::where('email', $email)->first();

        if (!$provider) {
            return null;
        }

        $reset = ProviderPasswordReset::where('provider_id', $provider->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$reset) {
            return null;
        }

        if (!Hash::check($token, $reset->token_hash)) {
            return null;
        }

        return $reset;
    }

    public function invalidatePrevious(int $providerId): void
    {
        ProviderPasswordReset::where('provider_id', $providerId)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['used_at' => now()]);
    }

    public function markUsed(int $id): bool
    {
        $reset = ProviderPasswordReset::find($id);
        if ($reset) {
            $reset->update(['used_at' => now()]);
            return true;
        }
        return false;
    }
}
