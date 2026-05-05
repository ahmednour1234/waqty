<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Admin\AdminBannerStoreRequest;
use App\Http\Requests\Admin\AdminBannerUpdateRequest;
use App\Http\Resources\Admin\AdminBannerResource;
use App\Services\AdminBannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Admin')]
#[Subgroup('Banners', 'Promotional banner management with image upload, placement, and scheduling')]
class AdminBannerController extends Controller
{
    public function __construct(
        private AdminBannerService $service,
    ) {}

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[QueryParam('search', 'string', 'Search by banner title', required: false)]
    #[QueryParam('active', 'boolean', 'Filter by active status', required: false)]
    #[QueryParam('placement', 'string', 'Filter by placement: home_top|home_bottom|home_middle|category|sidebar', required: false)]
    #[QueryParam('trashed', 'string', 'Pass "only" to list soft-deleted banners', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page (default 15)', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'search'    => $request->input('search'),
                'active'    => $request->has('active') ? filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN) : null,
                'placement' => $request->input('placement'),
                'trashed'   => $request->input('trashed'),
            ];

            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->service->index($filters, $perPage);

            return ApiResponse::success(
                AdminBannerResource::collection($paginated->items()),
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
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'title' => 'Summer Sale Banner', 'image_url' => 'https://...', 'placement' => 'home_top', 'dimensions' => '1200x400', 'active' => true, 'starts_at' => '2026-04-01', 'ends_at' => '2026-04-30']], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function show(string $uuid): JsonResponse
    {
        try {
            $banner = $this->service->show($uuid);
            return ApiResponse::success(new AdminBannerResource($banner));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Banner not found.', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('title', 'string', 'Banner title', required: true, example: 'Summer Sale Banner')]
    #[BodyParam('image', 'file', 'Banner image (jpeg/png/webp, max 2 MB)', required: false)]
    #[BodyParam('placement', 'string', 'Placement: home_top|home_bottom|home_middle|category|sidebar (default: home_top)', required: false, example: 'home_top')]
    #[BodyParam('dimensions', 'string', 'Dimensions: 1200x400|1200x600|800x400|600x300 (default: 1200x400)', required: false, example: '1200x400')]
    #[BodyParam('active', 'boolean', 'Publish immediately (default: true)', required: false, example: true)]
    #[BodyParam('sort_order', 'integer', 'Display order (lower = first)', required: false, example: 0)]
    #[BodyParam('starts_at', 'string', 'Start date (YYYY-MM-DD)', required: false, example: '2026-04-01')]
    #[BodyParam('ends_at', 'string', 'End date (YYYY-MM-DD)', required: false, example: '2026-04-30')]
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'title' => 'Summer Sale Banner', 'placement' => 'home_top', 'dimensions' => '1200x400', 'active' => true]], 201)]
    #[Response(['success' => false, 'message' => 'The given data was invalid.', 'errors' => []], 422)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function store(AdminBannerStoreRequest $request): JsonResponse
    {
        try {
            $admin  = $request->user('admin');
            $data   = $request->validated();
            $data['image'] = $request->file('image');
            $banner = $this->service->create($data, $admin->id);
            return ApiResponse::success(new AdminBannerResource($banner), null, 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('title', 'string', 'Banner title', required: false)]
    #[BodyParam('image', 'file', 'New banner image (replaces existing)', required: false)]
    #[BodyParam('placement', 'string', 'Placement: home_top|home_bottom|home_middle|category|sidebar', required: false)]
    #[BodyParam('dimensions', 'string', 'Dimensions: 1200x400|1200x600|800x400|600x300', required: false)]
    #[BodyParam('sort_order', 'integer', 'Display order', required: false)]
    #[BodyParam('starts_at', 'string', 'Start date (YYYY-MM-DD), null to clear', required: false)]
    #[BodyParam('ends_at', 'string', 'End date (YYYY-MM-DD), null to clear', required: false)]
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'title' => 'Summer Sale Banner', 'active' => true]], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function update(AdminBannerUpdateRequest $request, string $uuid): JsonResponse
    {
        try {
            $data          = $request->validated();
            $data['image'] = $request->file('image');
            $banner        = $this->service->update($uuid, $data);
            return ApiResponse::success(new AdminBannerResource($banner));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Banner not found.', 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation error.', 422, $e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('active', 'boolean', 'true = publish, false = hide', required: true, example: false)]
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'active' => false]], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function setActive(Request $request, string $uuid): JsonResponse
    {
        if (!$request->has('active')) {
            return ApiResponse::error('The active field is required.', 400);
        }

        try {
            $active = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN);
            $banner = $this->service->setActive($uuid, $active);
            return ApiResponse::success(new AdminBannerResource($banner));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Banner not found.', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'message' => 'Banner deleted successfully.'], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->service->destroy($uuid);
            return ApiResponse::success(null, 'Banner deleted successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Banner not found.', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
