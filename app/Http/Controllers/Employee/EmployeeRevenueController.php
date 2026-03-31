<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Employee\EmployeeRevenueRequest;
use App\Services\RevenueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Employee')]
#[Subgroup('Revenue', 'Employee revenue analytics')]
class EmployeeRevenueController extends Controller
{
    public function __construct(
        private RevenueService $revenueService
    ) {
    }

    /**
     * Get the authenticated employee's total revenue from completed bookings.
     */
    public function index(EmployeeRevenueRequest $request): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();

            $data = $this->revenueService->getEmployeeRevenue(
                $employee,
                $request->input('start_date'),
                $request->input('end_date')
            );

            return ApiResponse::success($data);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
