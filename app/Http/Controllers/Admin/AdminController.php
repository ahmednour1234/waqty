<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Http\Requests\Admin\ToggleAdminActiveRequest;
use App\Http\Resources\AdminResource;
use App\Http\Helpers\ApiResponse;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;

#[Group('Admin APIs')]
class AdminController extends Controller
{
    public function __construct(
        private AdminService $adminService
    ) {
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[QueryParam('search', 'string', 'Search by name or email', required: false)]
    #[QueryParam('active', 'boolean', 'Filter by active status', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page', required: false, example: 15)]
    #[Response([
        'success' => true,
        'data' => [
            [
                'id' => 1,
                'name' => 'Admin Name',
                'email' => 'admin@example.com',
                'active' => true,
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'updated_at' => '2024-01-01T00:00:00.000000Z',
            ],
        ],
        'meta' => [
            'pagination' => [
                'current_page' => 1,
                'per_page' => 15,
                'total' => 1,
                'last_page' => 1,
            ],
        ],
    ], 200)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'search' => $request->input('search'),
                'active' => $request->has('active') ? filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN) : null,
            ];

            $perPage = (int) $request->input('per_page', 15);
            $paginated = $this->adminService->index($filters, $perPage);

            return ApiResponse::success(
                AdminResource::collection($paginated->items()),
                null,
                200,
                [
                    'pagination' => [
                        'current_page' => $paginated->currentPage(),
                        'per_page' => $paginated->perPage(),
                        'total' => $paginated->total(),
                        'last_page' => $paginated->lastPage(),
                    ],
                ]
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('name', 'string', 'Admin name', required: true, example: 'John Doe')]
    #[BodyParam('email', 'string', 'Admin email (must be unique)', required: true, example: 'admin@example.com')]
    #[BodyParam('password', 'string', 'Admin password (min 8 characters)', required: true, example: 'password123')]
    #[BodyParam('active', 'boolean', 'Active status', required: false, example: true)]
    #[Response([
        'success' => true,
        'message' => 'تم الإنشاء بنجاح',
        'data' => [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'admin@example.com',
            'active' => true,
            'created_at' => '2024-01-01T00:00:00.000000Z',
            'updated_at' => '2024-01-01T00:00:00.000000Z',
        ],
    ], 201)]
    #[Response(['success' => false, 'message' => 'فشل التحقق', 'errors' => ['email' => ['البريد الإلكتروني مطلوب']]], 422)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function store(StoreAdminRequest $request): JsonResponse
    {
        try {
            $admin = $this->adminService->store($request->validated());
            return ApiResponse::success(new AdminResource($admin), 'api.general.created', 201);
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
    ], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function show(int $id): JsonResponse
    {
        try {
            $admin = $this->adminService->show($id);
            return ApiResponse::success(new AdminResource($admin));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('name', 'string', 'Admin name', required: true, example: 'John Doe')]
    #[BodyParam('email', 'string', 'Admin email (must be unique)', required: true, example: 'admin@example.com')]
    #[BodyParam('password', 'string', 'Admin password (min 8 characters)', required: false, example: 'password123')]
    #[BodyParam('active', 'boolean', 'Active status', required: false, example: true)]
    #[Response([
        'success' => true,
        'message' => 'تم التحديث بنجاح',
        'data' => [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'admin@example.com',
            'active' => true,
            'created_at' => '2024-01-01T00:00:00.000000Z',
            'updated_at' => '2024-01-01T00:00:00.000000Z',
        ],
    ], 200)]
    #[Response(['success' => false, 'message' => 'فشل التحقق', 'errors' => ['email' => ['البريد الإلكتروني مطلوب']]], 422)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function update(UpdateAdminRequest $request, int $id): JsonResponse
    {
        try {
            $admin = $this->adminService->update($id, $request->validated());
            return ApiResponse::success(new AdminResource($admin), 'api.general.updated');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('active', 'boolean', 'Active status', required: true, example: true)]
    #[Response([
        'success' => true,
        'message' => 'تم التحديث بنجاح',
        'data' => [
            'id' => 1,
            'name' => 'Admin Name',
            'email' => 'admin@example.com',
            'active' => true,
            'created_at' => '2024-01-01T00:00:00.000000Z',
            'updated_at' => '2024-01-01T00:00:00.000000Z',
        ],
    ], 200)]
    #[Response(['success' => false, 'message' => 'فشل التحقق', 'errors' => ['active' => ['الحالة مطلوبة']]], 422)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function toggleActive(ToggleAdminActiveRequest $request, int $id): JsonResponse
    {
        try {
            $admin = $this->adminService->toggleActive($id, $request->validated()['active']);
            return ApiResponse::success(new AdminResource($admin), 'api.general.updated');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
