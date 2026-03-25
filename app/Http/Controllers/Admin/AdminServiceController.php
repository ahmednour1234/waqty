<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Admin\AdminServiceIndexRequest;
use App\Http\Requests\Admin\AdminServiceStatusRequest;
use App\Http\Resources\Admin\AdminServiceResource;
use App\Services\AdminServiceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Admin')]
#[Subgroup('Services', 'Manage services')]
class AdminServiceController extends Controller
{
    public function __construct(
        private AdminServiceService $service
    ) {}

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[QueryParam('provider_uuid', 'string', 'Filter by provider UUID', required: false)]
    #[QueryParam('sub_category_uuid', 'string', 'Filter by subcategory UUID', required: false)]
    #[QueryParam('category', 'string', 'Filter by plain category name (e.g. Hair, Skin)', required: false)]
    #[QueryParam('active', 'boolean', 'Filter by active status', required: false)]
    #[QueryParam('trashed', 'string', 'Include soft-deleted records (only|with)', required: false)]
    #[QueryParam('search', 'string', 'Search in service name/description (ar/en)', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    public function index(AdminServiceIndexRequest $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'provider_uuid', 'sub_category_uuid', 'category', 'active', 'trashed', 'search',
            ]);
            if ($request->has('active')) {
                $filters['active'] = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN);
            } else {
                unset($filters['active']);
            }

            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->service->index($filters, $perPage);

            return ApiResponse::success(
                AdminServiceResource::collection($paginated->items()),
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
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>']], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    public function show(string $uuid): JsonResponse
    {
        try {
            $service = $this->service->show($uuid);
            return ApiResponse::success(new AdminServiceResource($service));
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.services.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('active', 'boolean', 'Service active status', required: true)]
    #[Response(['success' => true, 'message' => 'تم التحديث بنجاح', 'data' => ['uuid' => '<ULID>']], 200)]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    public function updateStatus(AdminServiceStatusRequest $request, string $uuid): JsonResponse
    {
        try {
            $service = $this->service->updateStatus($uuid, $request->boolean('active'));
            return ApiResponse::success(new AdminServiceResource($service), 'api.general.updated');
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.services.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'message' => 'تم الحذف بنجاح'], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->service->destroy($uuid);
            return ApiResponse::success(null, 'api.services.deleted');
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.services.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'message' => 'تمت الاستعادة بنجاح', 'data' => ['uuid' => '<ULID>']], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    public function restore(string $uuid): JsonResponse
    {
        try {
            $service = $this->service->restore($uuid);
            return ApiResponse::success(new AdminServiceResource($service), 'api.services.restored');
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.services.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
