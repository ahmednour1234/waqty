<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Admin\AdminAnnouncementStoreRequest;
use App\Http\Requests\Admin\AdminAnnouncementUpdateRequest;
use App\Http\Resources\Admin\AdminAnnouncementResource;
use App\Services\AdminAnnouncementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Admin')]
#[Subgroup('Announcements', 'Platform-wide announcements targeting users, providers, employees, or branches')]
class AdminAnnouncementController extends Controller
{
    public function __construct(
        private AdminAnnouncementService $service,
    ) {}

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[QueryParam('search', 'string', 'Search in title or message', required: false)]
    #[QueryParam('active', 'boolean', 'Filter by active status', required: false)]
    #[QueryParam('target', 'string', 'Filter by target: all|users|providers|employees|branches', required: false)]
    #[QueryParam('priority', 'string', 'Filter by priority: low|normal|high|urgent', required: false)]
    #[QueryParam('trashed', 'string', 'Pass "only" to list soft-deleted announcements', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page (default 15)', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'search'   => $request->input('search'),
                'active'   => $request->has('active') ? filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN) : null,
                'target'   => $request->input('target'),
                'priority' => $request->input('priority'),
                'trashed'  => $request->input('trashed'),
            ];

            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->service->index($filters, $perPage);

            return ApiResponse::success(
                AdminAnnouncementResource::collection($paginated->items()),
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
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'title_en' => 'Scheduled Maintenance', 'title_ar' => 'صيانة مجدولة', 'message_en' => 'We will be performing maintenance...', 'target' => 'all', 'priority' => 'high', 'active' => true, 'ends_at' => '2026-04-30T00:00:00Z']], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function show(string $uuid): JsonResponse
    {
        try {
            $announcement = $this->service->show($uuid);
            return ApiResponse::success(new AdminAnnouncementResource($announcement));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Announcement not found.', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('title_en', 'string', 'Announcement title in English', required: true, example: 'Scheduled Maintenance')]
    #[BodyParam('title_ar', 'string', 'Announcement title in Arabic', required: true, example: 'صيانة مجدولة')]
    #[BodyParam('message_en', 'string', 'Announcement message in English', required: true)]
    #[BodyParam('message_ar', 'string', 'Announcement message in Arabic', required: true)]
    #[BodyParam('target', 'string', 'Target audience: all|users|providers|employees|branches (default: all)', required: false, example: 'all')]
    #[BodyParam('priority', 'string', 'Priority level: low|normal|high|urgent (default: normal)', required: false, example: 'normal')]
    #[BodyParam('active', 'boolean', 'Publish immediately (default: true)', required: false, example: true)]
    #[BodyParam('ends_at', 'string', 'Expiry date/time (ISO 8601)', required: false, example: '2026-04-30T00:00:00Z')]
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'title_en' => 'Scheduled Maintenance', 'target' => 'all', 'priority' => 'high', 'active' => true]], 201)]
    #[Response(['success' => false, 'message' => 'The given data was invalid.', 'errors' => []], 422)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function store(AdminAnnouncementStoreRequest $request): JsonResponse
    {
        try {
            $admin        = $request->user('admin');
            $announcement = $this->service->create($request->validated(), $admin->id);
            return ApiResponse::success(new AdminAnnouncementResource($announcement), null, 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('title_en', 'string', 'Announcement title in English', required: false)]
    #[BodyParam('title_ar', 'string', 'Announcement title in Arabic', required: false)]
    #[BodyParam('message_en', 'string', 'Announcement message in English', required: false)]
    #[BodyParam('message_ar', 'string', 'Announcement message in Arabic', required: false)]
    #[BodyParam('target', 'string', 'Target audience: all|users|providers|employees|branches', required: false)]
    #[BodyParam('priority', 'string', 'Priority level: low|normal|high|urgent', required: false)]
    #[BodyParam('ends_at', 'string', 'Expiry date/time (ISO 8601), pass null to clear', required: false)]
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'title_en' => 'Scheduled Maintenance', 'active' => true]], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function update(AdminAnnouncementUpdateRequest $request, string $uuid): JsonResponse
    {
        try {
            $announcement = $this->service->update($uuid, $request->validated());
            return ApiResponse::success(new AdminAnnouncementResource($announcement));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Announcement not found.', 404);
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
            $active       = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN);
            $announcement = $this->service->setActive($uuid, $active);
            return ApiResponse::success(new AdminAnnouncementResource($announcement));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Announcement not found.', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'message' => 'Announcement deleted successfully.'], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->service->destroy($uuid);
            return ApiResponse::success(null, 'Announcement deleted successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Announcement not found.', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
