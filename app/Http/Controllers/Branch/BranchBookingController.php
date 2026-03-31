<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Branch\BranchBookingGridRequest;
use App\Http\Requests\Branch\BranchBookingIndexRequest;
use App\Http\Requests\Branch\StoreBranchBookingRequest;
use App\Http\Resources\Provider\ProviderBookingResource;
use App\Services\BookingCreationService;
use App\Services\BookingScheduleGridService;
use App\Services\BranchBookingService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Branch')]
#[Subgroup('Bookings', 'Branch booking management')]
class BranchBookingController extends Controller
{
    public function __construct(
        private BookingCreationService $creationService,
        private BranchBookingService $bookingService,
        private BookingScheduleGridService $gridService
    ) {}

    public function index(BranchBookingIndexRequest $request): JsonResponse
    {
        try {
            $branch  = auth('branch')->user();
            $filters = $request->only(['status', 'employee_uuid', 'booking_date', 'from_date', 'to_date']);
            $perPage = (int) $request->input('per_page', 15);

            $paginated = $this->bookingService->index($branch, $filters, $perPage);

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
            $branch  = auth('branch')->user();
            $booking = $this->bookingService->show($branch, $uuid);

            return ApiResponse::success(new ProviderBookingResource($booking));
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function grid(BranchBookingGridRequest $request): JsonResponse
    {
        try {
            $branch = auth('branch')->user();
            $date   = $request->input('date');

            $grid = $this->gridService->gridForBranch($branch, $date);

            return ApiResponse::success($grid);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function nextUpcoming(): JsonResponse
    {
        try {
            $branch  = auth('branch')->user();
            $booking = $this->bookingService->nextUpcoming($branch);

            return ApiResponse::success($booking ? new ProviderBookingResource($booking) : null);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function store(StoreBranchBookingRequest $request): JsonResponse
    {
        try {
            $branch  = auth('branch')->user();
            $data    = $request->validated();

            $booking = $this->creationService->createByStaff(
                $branch->provider_id,
                $branch->id,
                $data
            );

            return ApiResponse::success(
                new ProviderBookingResource($booking->load(['provider', 'branch', 'employee', 'service'])),
                'api.bookings.created',
                201
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}

