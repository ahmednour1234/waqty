<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Http\Resources\AdminResource;
use App\Http\Helpers\ApiResponse;
use App\Services\AdminAuthService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Admin APIs', 'Admin authentication and management endpoints')]
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
