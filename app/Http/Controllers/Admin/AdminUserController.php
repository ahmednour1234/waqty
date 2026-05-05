<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Admin\AdminUserUpdateStatusRequest;
use App\Http\Resources\Admin\AdminUserResource;
use App\Services\AdminUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Admin')]
#[Subgroup('Users', 'End-user account management')]
class AdminUserController extends Controller
{
    public function __construct(
        private AdminUserService $adminUserService,
    ) {}

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[QueryParam('search', 'string', 'Search by name, email, or phone', required: false)]
    #[QueryParam('active', 'boolean', 'Filter by active status', required: false)]
    #[QueryParam('blocked', 'boolean', 'Filter by blocked status', required: false)]
    #[QueryParam('banned', 'boolean', 'Filter by banned status', required: false)]
    #[QueryParam('gender', 'string', 'Filter by gender (male|female)', required: false)]
    #[QueryParam('trashed', 'string', 'Include soft-deleted records: only|with', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page (default 15)', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'search'  => $request->input('search'),
                'active'  => $request->has('active')  ? filter_var($request->input('active'),  FILTER_VALIDATE_BOOLEAN) : null,
                'blocked' => $request->has('blocked') ? filter_var($request->input('blocked'), FILTER_VALIDATE_BOOLEAN) : null,
                'banned'  => $request->has('banned')  ? filter_var($request->input('banned'),  FILTER_VALIDATE_BOOLEAN) : null,
                'gender'  => $request->input('gender'),
                'trashed' => $request->input('trashed'),
            ];

            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->adminUserService->index($filters, $perPage);

            return ApiResponse::success(
                AdminUserResource::collection($paginated->items()),
                null,
                200,
                [
                    'pagination' => [
                        'current_page' => $paginated->currentPage(),
                        'per_page'     => $paginated->perPage(),
                        'total'        => $paginated->total(),
                        'last_page'    => $paginated->lastPage(),
                    ],
                ]
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'name' => 'User Name', 'email' => 'user@example.com', 'phone' => '+966500000000', 'gender' => 'male', 'active' => true, 'blocked' => false, 'banned' => false]], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function show(string $uuid): JsonResponse
    {
        try {
            $user = $this->adminUserService->show($uuid);
            return ApiResponse::success(new AdminUserResource($user));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('active', 'boolean', 'Active status', required: true, example: true)]
    #[Response(['success' => true, 'message' => 'تم التحديث بنجاح', 'data' => ['uuid' => '<ULID>', 'active' => true]], 200)]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function setActive(AdminUserUpdateStatusRequest $request, string $uuid): JsonResponse
    {
        try {
            $active = $request->validated()['active'] ?? null;
            if ($active === null) {
                return ApiResponse::error('api.general.validation_failed', 400);
            }
            $user = $this->adminUserService->setActive($uuid, (bool) $active);
            return ApiResponse::success(new AdminUserResource($user), 'api.general.updated');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('blocked', 'boolean', 'Blocked status', required: true, example: false)]
    #[Response(['success' => true, 'message' => 'تم التحديث بنجاح', 'data' => ['uuid' => '<ULID>', 'blocked' => false]], 200)]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function setBlocked(AdminUserUpdateStatusRequest $request, string $uuid): JsonResponse
    {
        try {
            $blocked = $request->validated()['blocked'] ?? null;
            if ($blocked === null) {
                return ApiResponse::error('api.general.validation_failed', 400);
            }
            $user = $this->adminUserService->setBlocked($uuid, (bool) $blocked);
            return ApiResponse::success(new AdminUserResource($user), 'api.general.updated');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('banned', 'boolean', 'Banned status', required: true, example: false)]
    #[Response(['success' => true, 'message' => 'تم التحديث بنجاح', 'data' => ['uuid' => '<ULID>', 'banned' => false]], 200)]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function setBanned(AdminUserUpdateStatusRequest $request, string $uuid): JsonResponse
    {
        try {
            $banned = $request->validated()['banned'] ?? null;
            if ($banned === null) {
                return ApiResponse::error('api.general.validation_failed', 400);
            }
            $user = $this->adminUserService->setBanned($uuid, (bool) $banned);
            return ApiResponse::success(new AdminUserResource($user), 'api.general.updated');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'message' => 'تم الحذف بنجاح'], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->adminUserService->destroy($uuid);
            return ApiResponse::success(null, 'api.general.deleted');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'message' => 'تم الاستعادة بنجاح', 'data' => ['uuid' => '<ULID>', 'name' => 'User Name']], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function restore(string $uuid): JsonResponse
    {
        try {
            $user = $this->adminUserService->restore($uuid);
            return ApiResponse::success(new AdminUserResource($user), 'api.general.restored');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
