<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\UserForgotPasswordRequest;
use App\Http\Requests\User\Auth\UserLoginRequest;
use App\Http\Requests\User\Auth\UserRegisterRequest;
use App\Http\Requests\User\Auth\UserResetPasswordRequest;
use App\Http\Requests\User\Auth\UserResendVerificationOtpRequest;
use App\Http\Requests\User\Auth\UserVerifyEmailRequest;
use App\Http\Requests\User\Auth\UserVerifyOtpRequest;
use App\Services\UserAuthService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('User', 'User authentication')]
class UserAuthController extends Controller
{
    public function __construct(protected UserAuthService $service)
    {
    }

    #[Unauthenticated]
    #[Subgroup('Auth - Register', 'Register new user')]
    public function register(UserRegisterRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('api.auth.register_success'),
            'data' => $this->service->register($request->validated()),
        ], 201);
    }

    #[Unauthenticated]
    #[Subgroup('Auth - Verify / Resend', 'Verify email OTP, resend OTP')]
    public function verifyEmail(UserVerifyEmailRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('api.auth.otp_verified'),
            'data' => $this->service->verifyEmail(
                $request->string('email')->toString(),
                $request->string('otp')->toString()
            ),
        ]);
    }

    #[Unauthenticated]
    #[Subgroup('Auth - Verify / Resend', 'Verify email OTP, resend OTP')]
    public function resendVerificationOtp(UserResendVerificationOtpRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('api.auth.otp_sent_generic'),
            'data' => $this->service->resendVerificationOtp(
                $request->string('email')->toString(),
                $request->ip(),
                $request->userAgent()
            ),
        ]);
    }

    #[Unauthenticated]
    #[Subgroup('Auth - Login', 'Login')]
    public function login(UserLoginRequest $request): JsonResponse
    {
        $result = $this->service->login(...$request->only(['login', 'password']));

        if (isset($result['success']) && $result['success'] === false) {
            $code = $result['status'] === 'invalid_credentials' ? 401 : 403;
            return response()->json([
                'success' => false,
                'status' => $result['status'],
                'message' => $result['message'],
            ], $code);
        }

        return response()->json([
            'success' => true,
            'message' => __('api.auth.login_success'),
            'data' => $result,
        ]);
    }

    #[Unauthenticated]
    #[Subgroup('Auth - Password', 'Forgot password, verify OTP, reset')]
    public function forgotPassword(UserForgotPasswordRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('api.auth.otp_sent_generic'),
            'data' => $this->service->forgotPassword(
                $request->string('email')->toString(),
                $request->ip(),
                $request->userAgent()
            ),
        ]);
    }

    #[Unauthenticated]
    #[Subgroup('Auth - Password', 'Forgot password, verify OTP, reset')]
    public function verifyOtp(UserVerifyOtpRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('api.auth.otp_verified'),
            'data' => $this->service->verifyOtp(...$request->only(['email', 'otp'])),
        ]);
    }

    #[Unauthenticated]
    #[Subgroup('Auth - Password', 'Forgot password, verify OTP, reset')]
    public function resetPassword(UserResetPasswordRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('api.auth.password_reset_success'),
            'data' => $this->service->resetPassword(
                $request->string('email')->toString(),
                $request->string('otp')->toString(),
                $request->string('password')->toString()
            ),
        ]);
    }

    #[Subgroup('Auth - Session', 'Logout and current user')]
    public function logout(): JsonResponse
    {
        $this->service->logout();

        return response()->json([
            'success' => true,
            'message' => __('api.auth.logout_success'),
            'data' => null,
        ]);
    }

    #[Subgroup('Auth - Session', 'Logout and current user')]
    public function me(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('api.auth.login_success'),
            'data' => $this->service->me(),
        ]);
    }
}
