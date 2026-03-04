<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubcategoryRequest;
use App\Http\Requests\Admin\ToggleSubcategoryActiveRequest;
use App\Http\Requests\Admin\UpdateSubcategoryRequest;
use App\Http\Resources\Admin\AdminSubcategoryResource;
use App\Http\Helpers\ApiResponse;
use App\Services\SubcategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;

#[Group('Admin APIs')]
class AdminSubcategoryController extends Controller
{
    public function __construct(
        private SubcategoryService $subcategoryService
    ) {
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[QueryParam('category_uuid', 'string', 'Filter by category UUID', required: false)]
    #[QueryParam('search', 'string', 'Search term', required: false)]
    #[QueryParam('active', 'boolean', 'Filter by active status', required: false)]
    #[QueryParam('trashed', 'string', 'Include soft-deleted records', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'category_uuid' => $request->input('category_uuid'),
                'search' => $request->input('search'),
                'active' => $request->has('active') ? filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN) : null,
                'trashed' => $request->input('trashed'),
            ];

            $perPage = (int) $request->input('per_page', 15);
            $paginated = $this->subcategoryService->index($filters, $perPage);

            return ApiResponse::success(
                AdminSubcategoryResource::collection($paginated->items()),
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
    #[BodyParam('category_uuid', 'string', 'Category UUID', required: true)]
    #[BodyParam('name.ar', 'string', 'Subcategory name in Arabic', required: true)]
    #[BodyParam('name.en', 'string', 'Subcategory name in English', required: true)]
    #[BodyParam('active', 'boolean', 'Active status', required: false)]
    #[BodyParam('sort_order', 'integer', 'Sort order', required: false)]
    #[BodyParam('image', 'file', 'Subcategory image (jpeg/png/webp, max 2MB)', required: false)]
    #[Response(['success' => true, 'message' => 'تم الإنشاء بنجاح', 'data' => ['uuid' => '<ULID>']], 201)]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function store(StoreSubcategoryRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $image = $request->file('image');

            $subcategory = $this->subcategoryService->store($data, $image);

            return ApiResponse::success(new AdminSubcategoryResource($subcategory->load('category')), 'api.general.created', 201);
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
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
            $subcategory = $this->subcategoryService->show($uuid);
            return ApiResponse::success(new AdminSubcategoryResource($subcategory->load('category')));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('category_uuid', 'string', 'Category UUID', required: false)]
    #[BodyParam('name.ar', 'string', 'Subcategory name in Arabic', required: false)]
    #[BodyParam('name.en', 'string', 'Subcategory name in English', required: false)]
    #[BodyParam('active', 'boolean', 'Active status', required: false)]
    #[BodyParam('sort_order', 'integer', 'Sort order', required: false)]
    #[BodyParam('image', 'file', 'Subcategory image (jpeg/png/webp, max 2MB)', required: false)]
    #[Response(['success' => true, 'message' => 'تم التحديث بنجاح', 'data' => ['uuid' => '<ULID>']], 200)]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function update(UpdateSubcategoryRequest $request, string $uuid): JsonResponse
    {
        try {
            $data = $request->validated();
            $image = $request->file('image');

            $subcategory = $this->subcategoryService->update($uuid, $data, $image);

            return ApiResponse::success(new AdminSubcategoryResource($subcategory->load('category')), 'api.general.updated');
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
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
            $this->subcategoryService->destroy($uuid);
            return ApiResponse::success(null, 'api.general.deleted');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('active', 'boolean', 'Active status', required: true)]
    #[Response(['success' => true, 'message' => 'تم التحديث بنجاح', 'data' => ['uuid' => '<ULID>']], 200)]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function toggleActive(ToggleSubcategoryActiveRequest $request, string $uuid): JsonResponse
    {
        try {
            $subcategory = $this->subcategoryService->toggleActive($uuid, $request->validated()['active']);
            return ApiResponse::success(new AdminSubcategoryResource($subcategory->load('category')), 'api.general.updated');
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
            $subcategory = $this->subcategoryService->restore($uuid);
            return ApiResponse::success(new AdminSubcategoryResource($subcategory->load('category')), 'api.general.restored');
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
            $this->subcategoryService->forceDelete($uuid);
            return ApiResponse::success(null, 'api.general.deleted');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
