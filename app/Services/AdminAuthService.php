<?php

namespace App\Services;

use App\Models\Admin;
use App\Repositories\Contracts\AdminRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthService
{
    public function __construct(
        private AdminRepositoryInterface $adminRepository
    ) {
    }

    public function login(string $email, string $password): array
    {
        $admin = $this->adminRepository->findByEmail($email);

        if (!$admin || !Hash::check($password, $admin->password)) {
            throw new \Exception('api.auth.invalid_credentials', 401);
        }

        if (!$admin->active) {
            throw new \Exception('api.auth.account_inactive', 403);
        }

        $token = Auth::guard('admin')->login($admin);
        $ttl = config('jwt.ttl') * 60;

        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttl,
            'admin' => $admin,
        ];
    }

    public function logout(): void
    {
        Auth::guard('admin')->logout();
    }

    public function me(): Admin
    {
        return Auth::guard('admin')->user();
    }
}
