<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\ToggleCategoryActiveRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Http\Resources\Admin\AdminCategoryResource;
use App\Http\Helpers\ApiResponse;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Admin')]
#[Subgroup('Categories', 'Categories CRUD')]
class AdminCategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService
    ) {
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
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
                'search' => $request->input('search'),
                'active' => $request->has('active') ? filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN) : null,
                'trashed' => $request->input('trashed'),
            ];

            $perPage = (int) $request->input('per_page', 15);
            $paginated = $this->categoryService->index($filters, $perPage);

            return ApiResponse::success(
                AdminCategoryResource::collection($paginated->items()),
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
    #[BodyParam('name.ar', 'string', 'Category name in Arabic', required: true)]
    #[BodyParam('name.en', 'string', 'Category name in English', required: true)]
    #[BodyParam('active', 'boolean', 'Active status', required: false)]
    #[BodyParam('sort_order', 'integer', 'Sort order', required: false)]
    #[BodyParam('image', 'file', 'Category image (jpeg/png/webp, max 2MB)', required: false)]
    #[Response(['success' => true, 'message' => 'تم الإنشاء بنجاح', 'data' => ['uuid' => '<ULID>']], 201)]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $image = $request->file('image');

            $category = $this->categoryService->store($data, $image);

            return ApiResponse::success(new AdminCategoryResource($category), 'api.general.created', 201);
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
            $category = $this->categoryService->show($uuid);
            return ApiResponse::success(new AdminCategoryResource($category->load('subcategories')));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('name.ar', 'string', 'Category name in Arabic', required: false)]
    #[BodyParam('name.en', 'string', 'Category name in English', required: false)]
    #[BodyParam('active', 'boolean', 'Active status', required: false)]
    #[BodyParam('sort_order', 'integer', 'Sort order', required: false)]
    #[BodyParam('image', 'file', 'Category image (jpeg/png/webp, max 2MB)', required: false)]
    #[Response(['success' => true, 'message' => 'تم التحديث بنجاح', 'data' => ['uuid' => '<ULID>']], 200)]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function update(UpdateCategoryRequest $request, string $uuid): JsonResponse
    {
        try {
            $data = $request->validated();
            $image = $request->file('image');

            $category = $this->categoryService->update($uuid, $data, $image);

            return ApiResponse::success(new AdminCategoryResource($category), 'api.general.updated');
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
            $this->categoryService->destroy($uuid);
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
    public function toggleActive(ToggleCategoryActiveRequest $request, string $uuid): JsonResponse
    {
        try {
            $category = $this->categoryService->toggleActive($uuid, $request->validated()['active']);
            return ApiResponse::success(new AdminCategoryResource($category), 'api.general.updated');
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
            $category = $this->categoryService->restore($uuid);
            return ApiResponse::success(new AdminCategoryResource($category), 'api.general.restored');
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
            $this->categoryService->forceDelete($uuid);
            return ApiResponse::success(null, 'api.general.deleted');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
