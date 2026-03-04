<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\EmployeeForgotPasswordRequest;
use App\Http\Requests\Employee\EmployeeLoginRequest;
use App\Http\Requests\Employee\EmployeeResetPasswordRequest;
use App\Http\Resources\Employee\EmployeeSelfResource;
use App\Http\Helpers\ApiResponse;
use App\Services\EmployeeAuthService;
use Illuminate\Http\JsonResponse;

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
