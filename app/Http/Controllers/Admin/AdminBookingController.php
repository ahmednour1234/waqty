<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Admin\AdminBookingIndexRequest;
use App\Http\Requests\Admin\AdminBookingStatusRequest;
use App\Http\Resources\Admin\AdminBookingResource;
use App\Services\AdminBookingService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Admin')]
#[Subgroup('Bookings', 'Admin booking management')]
class AdminBookingController extends Controller
{
    public function __construct(
        private AdminBookingService $bookingService
    ) {}

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[QueryParam('status', 'string', 'Filter by status (pending, confirmed, completed, cancelled, no_show).', required: false, example: 'pending')]
    #[QueryParam('user_uuid', 'string', 'Filter by user UUID.', required: false)]
    #[QueryParam('provider_uuid', 'string', 'Filter by provider UUID.', required: false)]
    #[QueryParam('branch_uuid', 'string', 'Filter by branch UUID.', required: false)]
    #[QueryParam('employee_uuid', 'string', 'Filter by employee UUID.', required: false)]
    #[QueryParam('booking_date', 'string', 'Filter by exact booking date (YYYY-MM-DD).', required: false, example: '2026-04-15')]
    #[QueryParam('from_date', 'string', 'Filter bookings on or after this date (YYYY-MM-DD).', required: false, example: '2026-04-01')]
    #[QueryParam('to_date', 'string', 'Filter bookings on or before this date (YYYY-MM-DD).', required: false, example: '2026-04-30')]
    #[QueryParam('trashed', 'string', 'Include soft-deleted records. Values: only, with.', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page (default 15).', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function index(AdminBookingIndexRequest $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'status', 'user_uuid', 'provider_uuid', 'branch_uuid', 'employee_uuid',
                'booking_date', 'from_date', 'to_date', 'trashed',
            ]);
            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->bookingService->index($filters, $perPage);

            return ApiResponse::success(
                AdminBookingResource::collection($paginated->items()),
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
    #[Response(['success' => true, 'data' => ['uuid' => '<UUID>', 'status' => 'confirmed', 'booking_date' => '2026-05-01']], 200)]
    #[Response(['success' => false, 'message' => 'Not found'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function show(string $uuid): JsonResponse
    {
        try {
            $booking = $this->bookingService->show($uuid);

            return ApiResponse::success(new AdminBookingResource($booking));
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('status', 'string', 'New booking status: pending, confirmed, completed, cancelled, no_show.', required: true, example: 'confirmed')]
    #[Response(['success' => true, 'message' => 'api.bookings.status_updated', 'data' => ['uuid' => '<UUID>', 'status' => 'confirmed']], 200)]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422)]
    #[Response(['success' => false, 'message' => 'Not found'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function updateStatus(AdminBookingStatusRequest $request, string $uuid): JsonResponse
    {
        try {
            $booking = $this->bookingService->updateStatus($uuid, $request->input('status'));

            return ApiResponse::success(
                new AdminBookingResource($booking),
                'api.bookings.status_updated'
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'message' => 'api.bookings.deleted'], 200)]
    #[Response(['success' => false, 'message' => 'Not found'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->bookingService->destroy($uuid);

            return ApiResponse::success(null, 'api.bookings.deleted');
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'data' => ['uuid' => '<UUID>', 'status' => 'confirmed', 'booking_date' => '2026-05-01']], 200)]
    #[Response(['success' => true, 'data' => null], 200, 'No upcoming booking found')]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function nextUpcoming(): JsonResponse
    {
        try {
            $booking = $this->bookingService->nextUpcoming();

            return ApiResponse::success($booking ? new AdminBookingResource($booking) : null);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
