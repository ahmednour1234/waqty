<?php

namespace App\Services;

use App\Models\Employee;
use App\Notifications\EmployeePasswordResetNotification;
use App\Repositories\Contracts\EmployeePasswordResetRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmployeeAuthService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private EmployeePasswordResetRepositoryInterface $passwordResetRepository
    ) {
    }

    public function login(string $email, string $password): array
    {
        $employee = $this->employeeRepository->findByEmail($email);

        if (!$employee || !Hash::check($password, $employee->password)) {
            throw new \Exception('api.auth.invalid_credentials', 401);
        }

        if (!$employee->active) {
            throw new \Exception('api.auth.account_inactive', 403);
        }

        if ($employee->blocked) {
            throw new \Exception('api.auth.account_blocked', 403);
        }

        $employee->update(['last_login_at' => now()]);

        $token = Auth::guard('employee')->login($employee);
        $ttl = config('jwt.ttl') * 60;

        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttl,
            'employee' => $employee,
        ];
    }

    public function logout(): void
    {
        Auth::guard('employee')->logout();
    }

    public function me(): Employee
    {
        return Auth::guard('employee')->user();
    }

    public function requestOtp(string $email, ?string $ip, ?string $userAgent): void
    {
        $employee = $this->employeeRepository->findByEmail($email);

        if ($employee) {
            $this->passwordResetRepository->invalidatePrevious($employee->id);

            $otp = random_int(100000, 999999);
            $otpHash = Hash::make((string) $otp);
            $expiresAt = now()->addMinutes(10);

            $this->passwordResetRepository->createOtp(
                $employee->id,
                $otpHash,
                $expiresAt,
                $ip,
                $userAgent
            );

            $employee->notify(new EmployeePasswordResetNotification($otp));
        }
    }

    public function resetPassword(string $email, string $otp, string $newPassword): void
    {
        $employee = $this->employeeRepository->findByEmail($email);

        if (!$employee) {
            throw new \Exception('api.auth.otp_invalid', 400);
        }

        $reset = $this->passwordResetRepository->findLatestValid($employee->id);

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

        $employee->update(['password' => $newPassword]);
        $this->passwordResetRepository->markUsed($reset->id);
    }
}
