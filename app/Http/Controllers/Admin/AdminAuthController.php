<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Http\Requests\Admin\AdminResendVerificationOtpRequest;
use App\Http\Requests\Admin\AdminVerifyEmailRequest;
use App\Http\Resources\AdminResource;
use App\Http\Helpers\ApiResponse;
use App\Services\AdminAuthService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Admin', 'Admin authentication and management')]
class AdminAuthController extends Controller
{
    public function __construct(
        private AdminAuthService $adminAuthService
    ) {
    }

    #[Unauthenticated]
    #[Header('Accept-Language', 'ar|en')]
    #[BodyParam('email', 'string', 'Admin email address', required: true, example: 'admin@example.com')]
    #[BodyParam('password', 'string', 'Admin password', required: true, example: 'password')]
    #[Response([
        'success' => true,
        'message' => 'تم تسجيل الدخول بنجاح',
        'data' => [
            'token' => '<JWT_TOKEN>',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'admin' => [
                'id' => 1,
                'name' => 'Admin Name',
                'email' => 'admin@example.com',
                'active' => true,
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'updated_at' => '2024-01-01T00:00:00.000000Z',
            ],
        ],
    ], 200, 'Login successful')]
    #[Response([
        'success' => false,
        'message' => 'بيانات الدخول غير صحيحة',
    ], 401, 'Invalid credentials')]
    #[Response([
        'success' => false,
        'message' => 'الحساب غير نشط',
    ], 403, 'Account inactive')]
    #[Subgroup('Auth - Verification', 'Send OTP, verify email, resend OTP')]
    public function sendVerificationOtp(AdminResendVerificationOtpRequest $request): JsonResponse
    {
        try {
            $this->adminAuthService->sendVerificationOtp(
                $request->string('email')->toString(),
                $request->ip(),
                $request->userAgent()
            );
            return ApiResponse::success(null, 'api.auth.otp_sent_generic');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    #[Subgroup('Auth - Verification', 'Send OTP, verify email, resend OTP')]
    public function verifyEmail(AdminVerifyEmailRequest $request): JsonResponse
    {
        try {
            $result = $this->adminAuthService->verifyEmail(
                $request->string('email')->toString(),
                $request->string('otp')->toString()
            );
            return ApiResponse::success([
                'token' => $result['token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
                'admin' => new AdminResource($result['admin']),
            ], 'api.auth.otp_verified');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    #[Subgroup('Auth - Verification', 'Send OTP, verify email, resend OTP')]
    public function resendVerificationOtp(AdminResendVerificationOtpRequest $request): JsonResponse
    {
        try {
            $this->adminAuthService->resendVerificationOtp(
                $request->string('email')->toString(),
                $request->ip(),
                $request->userAgent()
            );
            return ApiResponse::success(null, 'api.auth.otp_sent_generic');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    #[Subgroup('Auth - Login', 'Login with email and password')]
    public function login(AdminLoginRequest $request): JsonResponse
    {
        try {
            $result = $this->adminAuthService->login(
                $request->validated()['email'],
                $request->validated()['password']
            );

            return ApiResponse::success([
                'token' => $result['token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
                'admin' => new AdminResource($result['admin']),
            ], 'api.auth.login_success');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 401);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response([
        'success' => true,
        'message' => 'تم تسجيل الخروج بنجاح',
    ], 200, 'Logout successful')]
    #[Response([
        'success' => false,
        'message' => 'غير مصرح',
    ], 401, 'Unauthorized - Missing or invalid token')]
    #[Subgroup('Auth - Session', 'Logout and current user')]
    public function logout(): JsonResponse
    {
        try {
            $this->adminAuthService->logout();
            return ApiResponse::success(null, 'api.auth.logout_success');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response([
        'success' => true,
        'data' => [
            'id' => 1,
            'name' => 'Admin Name',
            'email' => 'admin@example.com',
            'active' => true,
            'created_at' => '2024-01-01T00:00:00.000000Z',
            'updated_at' => '2024-01-01T00:00:00.000000Z',
        ],
    ], 200, 'Get current admin')]
    #[Response([
        'success' => false,
        'message' => 'غير مصرح',
    ], 401, 'Unauthorized - Missing or invalid token')]
    #[Response([
        'success' => false,
        'message' => 'الحساب غير نشط',
    ], 403, 'Account inactive')]
    #[Subgroup('Auth - Session', 'Logout and current user')]
    public function me(): JsonResponse
    {
        try {
            $admin = $this->adminAuthService->me();
            return ApiResponse::success(new AdminResource($admin));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
