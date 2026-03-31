<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Employee\EmployeeBookingIndexRequest;
use App\Http\Requests\Employee\EmployeeBookingStatusRequest;
use App\Http\Resources\Employee\EmployeeBookingResource;
use App\Services\EmployeeBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Employee')]
#[Subgroup('Bookings', 'Employee booking management')]
class EmployeeBookingController extends Controller
{
    public function __construct(
        private EmployeeBookingService $bookingService
    ) {}

    public function index(EmployeeBookingIndexRequest $request): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();
            $filters  = $request->only(['status', 'booking_date', 'from_date', 'to_date']);

            if ($request->boolean('today')) {
                $filters['today'] = true;
            }

            if ($request->boolean('upcoming')) {
                $filters['upcoming'] = true;
            }

            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->bookingService->index($employee, $filters, $perPage);

            return ApiResponse::success(
                EmployeeBookingResource::collection($paginated->items()),
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
            $employee = Auth::guard('employee')->user();
            $booking  = $this->bookingService->show($employee, $uuid);

            return ApiResponse::success(new EmployeeBookingResource($booking));
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function updateStatus(EmployeeBookingStatusRequest $request, string $uuid): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();
            $booking  = $this->bookingService->updateStatus($employee, $uuid, $request->input('status'));

            return ApiResponse::success(
                new EmployeeBookingResource($booking),
                'api.bookings.status_updated'
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function nextUpcoming(): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();
            $booking  = $this->bookingService->nextUpcoming($employee);

            return ApiResponse::success($booking ? new EmployeeBookingResource($booking) : null);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
