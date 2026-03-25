<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetEmployeeBookingCountsRequest;
use App\Http\Resources\EmployeeBookingCountResource;
use App\Http\Helpers\ApiResponse;
use App\Services\BookingCountService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;
use Knuckles\Scribe\Attributes\QueryParam;

#[Group('Branch')]
#[Subgroup('Booking Analytics', 'Branch booking statistics')]
class BranchBookingCountController extends Controller
{
    public function __construct(
        private BookingCountService $bookingCountService
    ) {
    }

    /**
     * Get booking counts for each employee in the authenticated branch
     */
    #[QueryParam('start_date', 'Date in Y-m-d format for filtering bookings from this date', example: '2024-01-01')]
    #[QueryParam('end_date', 'Date in Y-m-d format for filtering bookings until this date', example: '2024-12-31')]
    public function employeeBookingCounts(GetEmployeeBookingCountsRequest $request): JsonResponse
    {
        try {
            $branch = auth('branch')->user();
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $data = $this->bookingCountService->getEmployeeBookingCountsByBranch(
                $branch,
                $startDate,
                $endDate
            );

            return ApiResponse::success(
                EmployeeBookingCountResource::collection($data),
                null,
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
