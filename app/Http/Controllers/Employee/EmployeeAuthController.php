<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\EmployeeForgotPasswordRequest;
use App\Http\Requests\Employee\EmployeeLoginRequest;
use App\Http\Requests\Employee\EmployeeResetPasswordRequest;
use App\Http\Requests\Employee\EmployeeVerifyOtpRequest;
use App\Http\Resources\Employee\EmployeeSelfResource;
use App\Http\Helpers\ApiResponse;
use App\Services\EmployeeAuthService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Employee APIs', 'Employee authentication and management endpoints')]
class EmployeeAuthController extends Controller
{
    public function __construct(
        private EmployeeAuthService $authService
    ) {
    }

    public function login(EmployeeLoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                $request->validated()['email'],
                $request->validated()['password']
            );

            return ApiResponse::success([
                'token' => $result['token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
                'employee' => new EmployeeSelfResource($result['employee']->load('branch')),
            ], 'api.auth.login_success');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 401);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();
            return ApiResponse::success(null, 'api.auth.logout_success');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function me(): JsonResponse
    {
        try {
            $employee = $this->authService->me();
            return ApiResponse::success(new EmployeeSelfResource($employee->load('branch')));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Unauthenticated]
    #[Header('Accept-Language', 'ar|en')]
    #[BodyParam('email', 'string', 'Employee email address', required: true, example: 'employee@example.com')]
    #[Response(['success' => true, 'message' => 'إذا كان البريد الإلكتروني موجوداً، سيتم إرسال رمز التحقق'], 200, 'Always returns generic success message to prevent email enumeration')]
    #[Response(['success' => false, 'message' => 'تم تجاوز الحد المسموح'], 429, 'Rate limited')]
    public function sendOtp(EmployeeForgotPasswordRequest $request): JsonResponse
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
    #[BodyParam('email', 'string', 'Employee email address', required: true, example: 'employee@example.com')]
    #[Response(['success' => true, 'message' => 'إذا كان البريد الإلكتروني موجوداً، سيتم إرسال رمز التحقق'], 200, 'Always returns generic success message to prevent email enumeration')]
    #[Response(['success' => false, 'message' => 'تم تجاوز الحد المسموح'], 429, 'Rate limited')]
    public function forgotPassword(EmployeeForgotPasswordRequest $request): JsonResponse
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
    #[BodyParam('email', 'string', 'Employee email address', required: true, example: 'employee@example.com')]
    #[BodyParam('otp', 'string', 'OTP verification code (6 digits)', required: true, example: '123456')]
    #[Response(['success' => true, 'message' => 'رمز التحقق صحيح', 'data' => ['valid' => true]], 200, 'OTP is valid')]
    #[Response(['success' => false, 'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية', 'data' => ['valid' => false]], 400, 'OTP is invalid or expired')]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422, 'Validation failed')]
    public function verifyOtp(EmployeeVerifyOtpRequest $request): JsonResponse
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
    #[BodyParam('email', 'string', 'Employee email address', required: true, example: 'employee@example.com')]
    #[BodyParam('otp', 'string', 'OTP verification code (6 digits)', required: true, example: '123456')]
    #[BodyParam('new_password', 'string', 'New password (min 8 characters)', required: true, example: 'newpassword123')]
    #[Response(['success' => true, 'message' => 'تم إعادة تعيين كلمة المرور بنجاح'], 200, 'Password reset successful')]
    #[Response(['success' => false, 'message' => 'الرمز غير صحيح أو منتهي الصلاحية'], 400, 'Invalid or expired OTP (generic message)')]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422, 'Validation failed')]
    #[Response(['success' => false, 'message' => 'تم تجاوز الحد المسموح'], 429, 'Rate limited')]
    public function resetPassword(EmployeeResetPasswordRequest $request): JsonResponse
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
