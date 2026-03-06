<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\UserForgotPasswordRequest;
use App\Http\Requests\User\Auth\UserLoginRequest;
use App\Http\Requests\User\Auth\UserRegisterRequest;
use App\Http\Requests\User\Auth\UserResetPasswordRequest;
use App\Http\Requests\User\Auth\UserVerifyOtpRequest;
use App\Services\UserAuthService;
use Illuminate\Http\JsonResponse;

class UserAuthController extends Controller
{
    public function __construct(protected UserAuthService $service)
    {
    }

    public function register(UserRegisterRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('api.auth.register_success'),
            'data' => $this->service->register($request->validated()),
        ]);
    }

    public function login(UserLoginRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('api.auth.login_success'),
            'data' => $this->service->login(...$request->only(['email', 'password'])),
        ]);
    }

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

    public function verifyOtp(UserVerifyOtpRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('api.auth.otp_verified'),
            'data' => $this->service->verifyOtp(...$request->only(['email', 'otp'])),
        ]);
    }

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

    public function logout(): JsonResponse
    {
        $this->service->logout();

        return response()->json([
            'success' => true,
            'message' => __('api.auth.logout_success'),
            'data' => null,
        ]);
    }

    public function me(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('api.auth.login_success'),
            'data' => $this->service->me(),
        ]);
    }
}
