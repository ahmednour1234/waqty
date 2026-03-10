<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUpdateBranchStatusRequest;
use App\Http\Resources\Admin\AdminProviderBranchResource;
use App\Http\Helpers\ApiResponse;
use App\Services\AdminProviderBranchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Admin')]
#[Subgroup('Provider Branches', 'Provider branches management')]
class AdminProviderBranchController extends Controller
{
    public function __construct(
        private AdminProviderBranchService $branchService
    ) {
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[QueryParam('provider_uuid', 'string', 'Filter by provider UUID', required: false)]
    #[QueryParam('country_uuid', 'string', 'Filter by country UUID', required: false)]
    #[QueryParam('city_uuid', 'string', 'Filter by city UUID', required: false)]
    #[QueryParam('category_uuid', 'string', 'Filter by category UUID', required: false)]
    #[QueryParam('active', 'boolean', 'Filter by active status', required: false)]
    #[QueryParam('blocked', 'boolean', 'Filter by blocked status', required: false)]
    #[QueryParam('banned', 'boolean', 'Filter by banned status', required: false)]
    #[QueryParam('is_main', 'boolean', 'Filter by main branch status', required: false)]
    #[QueryParam('trashed', 'string', 'Include soft-deleted records (only/with)', required: false)]
    #[QueryParam('search', 'string', 'Search term', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'provider_uuid' => $request->input('provider_uuid'),
                'country_uuid' => $request->input('country_uuid'),
                'city_uuid' => $request->input('city_uuid'),
                'category_uuid' => $request->input('category_uuid'),
                'active' => $request->has('active') ? filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN) : null,
                'blocked' => $request->has('blocked') ? filter_var($request->input('blocked'), FILTER_VALIDATE_BOOLEAN) : null,
                'banned' => $request->has('banned') ? filter_var($request->input('banned'), FILTER_VALIDATE_BOOLEAN) : null,
                'is_main' => $request->has('is_main') ? filter_var($request->input('is_main'), FILTER_VALIDATE_BOOLEAN) : null,
                'trashed' => $request->input('trashed'),
                'search' => $request->input('search'),
            ];

            $perPage = (int) $request->input('per_page', 15);
            $paginated = $this->branchService->index($filters, $perPage);

            return ApiResponse::success(
                AdminProviderBranchResource::collection($paginated->items()),
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
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>']], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function show(string $uuid): JsonResponse
    {
        try {
            $branch = $this->branchService->show($uuid);
            return ApiResponse::success(
                new AdminProviderBranchResource($branch->load(['provider', 'country', 'city']))
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.branches.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('active', 'boolean', 'Active status', required: false)]
    #[BodyParam('blocked', 'boolean', 'Blocked status', required: false)]
    #[BodyParam('banned', 'boolean', 'Banned status', required: false)]
    #[Response(['success' => true, 'message' => 'تم التحديث بنجاح', 'data' => ['uuid' => '<ULID>']], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function updateStatus(AdminUpdateBranchStatusRequest $request, string $uuid): JsonResponse
    {
        try {
            $status = $request->validated();
            $branch = $this->branchService->updateStatus($uuid, $status);

            return ApiResponse::success(
                new AdminProviderBranchResource($branch->load(['provider', 'country', 'city'])),
                'api.branches.updated'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.branches.not_found', 404);
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
            $this->branchService->destroy($uuid);
            return ApiResponse::success(null, 'api.branches.deleted');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.branches.not_found', 404);
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
            $branch = $this->branchService->restore($uuid);
            return ApiResponse::success(
                new AdminProviderBranchResource($branch->load(['provider', 'country', 'city'])),
                'api.branches.restored'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.branches.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
