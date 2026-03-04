<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\ProviderForgotPasswordRequest;
use App\Http\Requests\Provider\ProviderLoginRequest;
use App\Http\Requests\Provider\ProviderResetPasswordRequest;
use App\Http\Resources\Provider\ProviderSelfResource;
use App\Http\Helpers\ApiResponse;
use App\Services\ProviderAuthService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Provider APIs', 'Provider authentication and management endpoints')]
class ProviderAuthController extends Controller
{
    public function __construct(
        private ProviderAuthService $providerAuthService
    ) {
    }

    #[Unauthenticated]
    #[Header('Accept-Language', 'ar|en')]
    #[BodyParam('email', 'string', 'Provider email address', required: true, example: 'provider@example.com')]
    #[BodyParam('password', 'string', 'Provider password', required: true, example: 'password')]
    #[Response([
        'success' => true,
        'message' => 'تم تسجيل الدخول بنجاح',
        'data' => [
            'token' => '<JWT_TOKEN>',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'provider' => ['uuid' => '<ULID>', 'name' => 'Provider Name'],
        ],
    ], 200, 'Login successful')]
    #[Response(['success' => false, 'message' => 'بيانات الدخول غير صحيحة'], 401, 'Invalid credentials')]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط أو محظور أو محظور'], 403, 'Account inactive, blocked, or banned')]
    public function login(ProviderLoginRequest $request): JsonResponse
    {
        try {
            $result = $this->providerAuthService->login(
                $request->validated()['email'],
                $request->validated()['password']
            );

            return ApiResponse::success([
                'token' => $result['token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
                'provider' => new ProviderSelfResource($result['provider']),
            ], 'api.auth.login_success');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 401);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'message' => 'تم تسجيل الخروج بنجاح'], 200)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط أو محظور أو محظور'], 403)]
    public function logout(): JsonResponse
    {
        try {
            $this->providerAuthService->logout();
            return ApiResponse::success(null, 'api.auth.logout_success');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'name' => 'Provider Name']], 200)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط أو محظور أو محظور'], 403)]
    public function me(): JsonResponse
    {
        try {
            $provider = $this->providerAuthService->me();
            $provider->load(['category', 'country', 'city']);
            return ApiResponse::success(new ProviderSelfResource($provider));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Unauthenticated]
    #[Header('Accept-Language', 'ar|en')]
    #[BodyParam('email', 'string', 'Provider email address', required: true, example: 'provider@example.com')]
    #[Response(['success' => true, 'message' => 'إذا كان البريد الإلكتروني موجوداً، سيتم إرسال رابط إعادة تعيين كلمة المرور'], 200, 'Always returns generic success message to prevent email enumeration')]
    #[Response(['success' => false, 'message' => 'تم تجاوز الحد المسموح'], 429, 'Rate limited')]
    public function forgotPassword(ProviderForgotPasswordRequest $request): JsonResponse
    {
        try {
            $this->providerAuthService->requestReset(
                $request->validated()['email'],
                $request->ip(),
                $request->userAgent()
            );

            return ApiResponse::success(null, 'api.auth.reset_sent_generic');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Unauthenticated]
    #[Header('Accept-Language', 'ar|en')]
    #[BodyParam('email', 'string', 'Provider email address', required: true, example: 'provider@example.com')]
    #[BodyParam('token', 'string', 'Password reset token', required: true, example: '<RESET_TOKEN>')]
    #[BodyParam('new_password', 'string', 'New password (min 8 characters)', required: true, example: 'newpassword123')]
    #[Response(['success' => true, 'message' => 'تم إعادة تعيين كلمة المرور بنجاح'], 200, 'Password reset successful')]
    #[Response(['success' => false, 'message' => 'الرمز غير صحيح أو منتهي الصلاحية'], 400, 'Invalid or expired token (generic message)')]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422, 'Validation failed')]
    #[Response(['success' => false, 'message' => 'تم تجاوز الحد المسموح'], 429, 'Rate limited')]
    public function resetPassword(ProviderResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->providerAuthService->resetPassword(
                $request->validated()['email'],
                $request->validated()['token'],
                $request->validated()['new_password']
            );

            return ApiResponse::success(null, 'api.auth.password_reset_success');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
