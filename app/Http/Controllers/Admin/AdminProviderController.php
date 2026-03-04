<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminProviderDeleteRequest;
use App\Http\Requests\Admin\AdminProviderUpdateStatusRequest;
use App\Http\Resources\Admin\AdminProviderResource;
use App\Http\Helpers\ApiResponse;
use App\Services\AdminProviderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;

#[Group('Admin APIs')]
class AdminProviderController extends Controller
{
    public function __construct(
        private AdminProviderService $adminProviderService
    ) {
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[QueryParam('search', 'string', 'Search term', required: false)]
    #[QueryParam('active', 'boolean', 'Filter by active status', required: false)]
    #[QueryParam('blocked', 'boolean', 'Filter by blocked status', required: false)]
    #[QueryParam('banned', 'boolean', 'Filter by banned status', required: false)]
    #[QueryParam('country_id', 'integer', 'Filter by country ID', required: false)]
    #[QueryParam('city_id', 'integer', 'Filter by city ID', required: false)]
    #[QueryParam('category_id', 'integer', 'Filter by category ID', required: false)]
    #[QueryParam('trashed', 'boolean', 'Include soft-deleted records', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'search' => $request->input('search'),
                'active' => $request->has('active') ? filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN) : null,
                'blocked' => $request->has('blocked') ? filter_var($request->input('blocked'), FILTER_VALIDATE_BOOLEAN) : null,
                'banned' => $request->has('banned') ? filter_var($request->input('banned'), FILTER_VALIDATE_BOOLEAN) : null,
                'country_id' => $request->input('country_id'),
                'city_id' => $request->input('city_id'),
                'category_id' => $request->input('category_id'),
                'trashed' => $request->input('trashed'),
            ];

            $perPage = (int) $request->input('per_page', 15);
            $paginated = $this->adminProviderService->index($filters, $perPage);

            return ApiResponse::success(
                AdminProviderResource::collection($paginated->items()),
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
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'name' => 'Provider Name']], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function show(string $uuid): JsonResponse
    {
        try {
            $provider = $this->adminProviderService->show($uuid);
            return ApiResponse::success(new AdminProviderResource($provider));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
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
    public function toggleActive(AdminProviderUpdateStatusRequest $request, string $uuid): JsonResponse
    {
        try {
            $active = $request->validated()['active'] ?? null;
            if ($active === null) {
                return ApiResponse::error('api.general.validation_failed', 400);
            }
            $provider = $this->adminProviderService->toggleActive($uuid, $active);
            return ApiResponse::success(new AdminProviderResource($provider), 'api.general.updated');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
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
    public function setBlocked(AdminProviderUpdateStatusRequest $request, string $uuid): JsonResponse
    {
        try {
            $blocked = $request->validated()['blocked'] ?? null;
            if ($blocked === null) {
                return ApiResponse::error('api.general.validation_failed', 400);
            }
            $provider = $this->adminProviderService->setBlocked($uuid, $blocked);
            return ApiResponse::success(new AdminProviderResource($provider), 'api.general.updated');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
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
    public function setBanned(AdminProviderUpdateStatusRequest $request, string $uuid): JsonResponse
    {
        try {
            $banned = $request->validated()['banned'] ?? null;
            if ($banned === null) {
                return ApiResponse::error('api.general.validation_failed', 400);
            }
            $provider = $this->adminProviderService->setBanned($uuid, $banned);
            return ApiResponse::success(new AdminProviderResource($provider), 'api.general.updated');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
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
            $this->adminProviderService->delete($uuid);
            return ApiResponse::success(null, 'api.general.deleted');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'message' => 'تم الاستعادة بنجاح', 'data' => ['uuid' => '<ULID>']], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function restore(string $uuid): JsonResponse
    {
        try {
            $provider = $this->adminProviderService->restore($uuid);
            $provider->load(['category', 'country', 'city']);
            return ApiResponse::success(new AdminProviderResource($provider), 'api.general.restored');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'message' => 'تم الحذف نهائياً'], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function forceDelete(string $uuid): JsonResponse
    {
        try {
            $this->adminProviderService->forceDelete($uuid);
            return ApiResponse::success(null, 'api.general.deleted');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
