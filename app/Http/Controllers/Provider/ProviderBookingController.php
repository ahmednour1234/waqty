<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Provider\ProviderBookingIndexRequest;
use App\Http\Requests\Provider\UpdateProviderBookingStatusRequest;
use App\Http\Resources\Provider\ProviderBookingResource;
use App\Services\ProviderBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Provider')]
#[Subgroup('Bookings', 'Provider booking management')]
class ProviderBookingController extends Controller
{
    public function __construct(
        private ProviderBookingService $bookingService
    ) {}

    public function index(ProviderBookingIndexRequest $request): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();
            $filters  = $request->only(['status', 'branch_uuid', 'employee_uuid', 'booking_date', 'from_date', 'to_date']);
            $perPage  = (int) $request->input('per_page', 15);

            $paginated = $this->bookingService->index($provider, $filters, $perPage);

            return ApiResponse::success(
                ProviderBookingResource::collection($paginated->items()),
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
            $provider = Auth::guard('provider')->user();
            $booking  = $this->bookingService->show($provider, $uuid);

            return ApiResponse::success(new ProviderBookingResource($booking));
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function updateStatus(UpdateProviderBookingStatusRequest $request, string $uuid): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();
            $booking  = $this->bookingService->updateStatus($provider, $uuid, $request->input('status'));

            return ApiResponse::success(
                new ProviderBookingResource($booking),
                'api.bookings.status_updated'
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
