<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Resources\Admin\AdminRatingResource;
use App\Services\AdminRatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Admin')]
#[Subgroup('Ratings', 'End-user rating and review moderation')]
class AdminRatingController extends Controller
{
    public function __construct(
        private AdminRatingService $adminRatingService,
    ) {}

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[QueryParam('search', 'string', 'Search in comment, user name or email', required: false)]
    #[QueryParam('active', 'boolean', 'Filter by published (true) / hidden (false)', required: false)]
    #[QueryParam('rating', 'integer', 'Filter by star rating (1–5)', required: false)]
    #[QueryParam('provider_uuid', 'string', 'Filter by provider UUID', required: false)]
    #[QueryParam('trashed', 'string', 'Pass "only" to list soft-deleted ratings', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page (default 15)', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'search'        => $request->input('search'),
                'active'        => $request->has('active') ? filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN) : null,
                'rating'        => $request->input('rating'),
                'provider_uuid' => $request->input('provider_uuid'),
                'trashed'       => $request->input('trashed'),
            ];

            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->adminRatingService->index($filters, $perPage);

            return ApiResponse::success(
                AdminRatingResource::collection($paginated->items()),
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
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'rating' => 5, 'comment' => 'Great service!', 'active' => true, 'user' => ['uuid' => '<ULID>', 'name' => 'Ahmed', 'email' => 'ahmed@example.com'], 'booking' => ['uuid' => '<ULID>', 'booking_date' => '2026-04-12']]], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function show(string $uuid): JsonResponse
    {
        try {
            $rating = $this->adminRatingService->show($uuid);
            return ApiResponse::success(new AdminRatingResource($rating));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('active', 'boolean', 'true = publish, false = hide', required: true, example: false)]
    #[Response(['success' => true, 'message' => 'تم التحديث بنجاح', 'data' => ['uuid' => '<ULID>', 'active' => false]], 200)]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function setActive(Request $request, string $uuid): JsonResponse
    {
        try {
            $active = $request->input('active');

            if ($active === null) {
                return ApiResponse::error('api.general.validation_failed', 400);
            }

            $rating = $this->adminRatingService->setActive($uuid, filter_var($active, FILTER_VALIDATE_BOOLEAN));
            return ApiResponse::success(new AdminRatingResource($rating), 'api.general.updated');
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
            $this->adminRatingService->destroy($uuid);
            return ApiResponse::success(null, 'api.general.deleted');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'data' => ['total' => 10, 'published' => 6, 'hidden' => 2, 'avg_rating' => 4.2]], 200)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function stats(): JsonResponse
    {
        try {
            return ApiResponse::success($this->adminRatingService->stats());
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
