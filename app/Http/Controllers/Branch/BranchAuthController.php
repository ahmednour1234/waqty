<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Branch\BranchForgotPasswordRequest;
use App\Http\Requests\Branch\BranchLoginRequest;
use App\Http\Requests\Branch\BranchResetPasswordRequest;
use App\Http\Requests\Branch\BranchVerifyOtpRequest;
use App\Http\Resources\Branch\BranchSelfResource;
use App\Services\BranchAuthService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Branch', 'Provider Branch independent authentication')]
class BranchAuthController extends Controller
{
    public function __construct(
        private BranchAuthService $authService
    ) {
    }

    #[Unauthenticated]
    #[Header('Accept-Language', 'ar|en')]
    #[BodyParam('email', 'string', 'Branch email address', required: true, example: 'provider1@example.com')]
    #[BodyParam('password', 'string', 'Branch password', required: true, example: 'password123')]
    #[Response(['success' => true, 'message' => 'تم تسجيل الدخول بنجاح', 'data' => ['token' => '...', 'token_type' => 'Bearer', 'expires_in' => 3600]], 200, 'Login successful')]
    #[Response(['success' => false, 'message' => 'بيانات الاعتماد غير صحيحة'], 401, 'Invalid credentials')]
    #[Subgroup('Auth - Login', 'Login with branch email and password')]
    public function login(BranchLoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                $request->validated()['email'],
                $request->validated()['password']
            );

            return ApiResponse::success([
                'token'      => $result['token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
                'branch'     => new BranchSelfResource($result['branch']->load('provider')),
            ], 'api.auth.login_success');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 401);
        }
    }

    #[Subgroup('Auth - Session', 'Logout and current branch')]
    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();
            return ApiResponse::success(null, 'api.auth.logout_success');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Subgroup('Auth - Session', 'Logout and current branch')]
    public function me(): JsonResponse
    {
        try {
            $branch = $this->authService->me();
            return ApiResponse::success(new BranchSelfResource($branch->load('provider')));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Unauthenticated]
    #[Header('Accept-Language', 'ar|en')]
    #[BodyParam('email', 'string', 'Branch email address', required: true, example: 'provider1@example.com')]
    #[Response(['success' => true, 'message' => 'إذا كان البريد الإلكتروني موجوداً، سيتم إرسال رمز التحقق'], 200, 'OTP sent (generic message to prevent email enumeration)')]
    #[Response(['success' => false, 'message' => 'تم تجاوز الحد المسموح'], 429, 'Rate limited')]
    #[Subgroup('Auth - Password', 'Forgot password, verify OTP, reset password')]
    public function forgotPassword(BranchForgotPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->requestOtp(
                $request->validated()['email'],
                $request->ip(),
                $request->userAgent()
            );

            return ApiResponse::success(null, 'api.auth.otp_sent_generic');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Unauthenticated]
    #[Header('Accept-Language', 'ar|en')]
    #[BodyParam('email', 'string', 'Branch email address', required: true, example: 'provider1@example.com')]
    #[BodyParam('otp', 'string', 'OTP code (6 digits)', required: true, example: '111111')]
    #[Response(['success' => true, 'data' => ['valid' => true]], 200, 'OTP is valid')]
    #[Response(['success' => false, 'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية'], 400, 'OTP invalid or expired')]
    #[Subgroup('Auth - Password', 'Forgot password, verify OTP, reset password')]
    public function verifyOtp(BranchVerifyOtpRequest $request): JsonResponse
    {
        try {
            $isValid = $this->authService->verifyOtp(
                $request->validated()['email'],
                $request->validated()['otp']
            );

            if ($isValid) {
                return ApiResponse::success(['valid' => true], 'api.auth.otp_valid');
            }

            return ApiResponse::error('api.auth.otp_invalid', 400);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    #[Unauthenticated]
    #[Header('Accept-Language', 'ar|en')]
    #[BodyParam('email', 'string', 'Branch email address', required: true, example: 'provider1@example.com')]
    #[BodyParam('otp', 'string', 'OTP code (6 digits)', required: true, example: '111111')]
    #[BodyParam('new_password', 'string', 'New password (min 8 characters)', required: true, example: 'newpassword123')]
    #[BodyParam('new_password_confirmation', 'string', 'Confirm new password', required: true, example: 'newpassword123')]
    #[Response(['success' => true, 'message' => 'تم إعادة تعيين كلمة المرور بنجاح'], 200, 'Password reset successfully')]
    #[Response(['success' => false, 'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية'], 400, 'OTP invalid')]
    #[Subgroup('Auth - Password', 'Forgot password, verify OTP, reset password')]
    public function resetPassword(BranchResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->resetPassword(
                $request->validated()['email'],
                $request->validated()['otp'],
                $request->validated()['new_password']
            );

            return ApiResponse::success(null, 'api.auth.password_reset_success');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
