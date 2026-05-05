<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Admin\AdminContentPageStoreRequest;
use App\Http\Requests\Admin\AdminContentPageUpdateRequest;
use App\Http\Resources\Admin\AdminContentPageResource;
use App\Services\AdminContentPageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Admin')]
#[Subgroup('Content Pages', 'Static content pages management (Terms, Privacy Policy, FAQ, About)')]
class AdminContentPageController extends Controller
{
    public function __construct(
        private AdminContentPageService $service,
    ) {}

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'data' => [['uuid' => '<ULID>', 'slug' => 'terms-conditions', 'title_en' => 'Terms & Conditions', 'title_ar' => 'الشروط والأحكام', 'active' => true, 'updated_by' => ['uuid' => '<ULID>', 'name' => 'Platform Admin'], 'updated_at' => '2026-04-01T00:00:00Z']]], 200)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function index(): JsonResponse
    {
        try {
            $pages = $this->service->index();
            return ApiResponse::success(AdminContentPageResource::collection($pages));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'slug' => 'terms-conditions', 'title_en' => 'Terms & Conditions', 'title_ar' => 'الشروط والأحكام', 'content_en' => 'Full terms content...', 'content_ar' => 'محتوى الشروط الكامل...', 'active' => true, 'updated_by' => ['uuid' => '<ULID>', 'name' => 'Platform Admin'], 'created_at' => '2026-01-01T00:00:00Z', 'updated_at' => '2026-04-01T00:00:00Z']], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function show(string $uuid): JsonResponse
    {
        try {
            $page = $this->service->show($uuid);
            return ApiResponse::success(new AdminContentPageResource($page));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Content page not found.', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('slug', 'string', 'URL-friendly slug (lowercase, hyphens only)', required: true, example: 'terms-conditions')]
    #[BodyParam('title_en', 'string', 'Page title in English', required: true, example: 'Terms & Conditions')]
    #[BodyParam('title_ar', 'string', 'Page title in Arabic', required: true, example: 'الشروط والأحكام')]
    #[BodyParam('content_en', 'string', 'Full page content in English', required: false)]
    #[BodyParam('content_ar', 'string', 'Full page content in Arabic', required: false)]
    #[BodyParam('active', 'boolean', 'Publish immediately (default: true)', required: false, example: true)]
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'slug' => 'terms-conditions', 'title_en' => 'Terms & Conditions', 'title_ar' => 'الشروط والأحكام', 'active' => true]], 201)]
    #[Response(['success' => false, 'message' => 'Validation error.', 'errors' => ['slug' => ['A page with this slug already exists.']]], 422)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function store(AdminContentPageStoreRequest $request): JsonResponse
    {
        try {
            $admin = $request->user('admin');
            $page  = $this->service->create($request->validated(), $admin->id);
            return ApiResponse::success(new AdminContentPageResource($page), null, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation error.', 422, $e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('slug', 'string', 'URL-friendly slug (lowercase, hyphens only)', required: false, example: 'terms-conditions')]
    #[BodyParam('title_en', 'string', 'Page title in English', required: false, example: 'Terms & Conditions')]
    #[BodyParam('title_ar', 'string', 'Page title in Arabic', required: false, example: 'الشروط والأحكام')]
    #[BodyParam('content_en', 'string', 'Full page content in English', required: false)]
    #[BodyParam('content_ar', 'string', 'Full page content in Arabic', required: false)]
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'slug' => 'terms-conditions', 'title_en' => 'Terms & Conditions', 'title_ar' => 'الشروط والأحكام', 'active' => true, 'updated_by' => ['uuid' => '<ULID>', 'name' => 'Platform Admin']]], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function update(AdminContentPageUpdateRequest $request, string $uuid): JsonResponse
    {
        try {
            $admin = $request->user('admin');
            $page  = $this->service->update($uuid, $request->validated(), $admin->id);
            return ApiResponse::success(new AdminContentPageResource($page));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Content page not found.', 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation error.', 422, $e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('active', 'boolean', 'true = publish, false = hide', required: true, example: true)]
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'active' => false]], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function setActive(Request $request, string $uuid): JsonResponse
    {
        if (!$request->has('active')) {
            return ApiResponse::error('The active field is required.', 400);
        }

        try {
            $admin  = $request->user('admin');
            $active = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN);
            $page   = $this->service->setActive($uuid, $active, $admin->id);
            return ApiResponse::success(new AdminContentPageResource($page));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Content page not found.', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
