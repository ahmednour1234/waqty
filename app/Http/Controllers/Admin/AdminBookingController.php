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
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Admin')]
#[Subgroup('Bookings', 'Admin booking management')]
class AdminBookingController extends Controller
{
    public function __construct(
        private AdminBookingService $bookingService
    ) {}

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
}
