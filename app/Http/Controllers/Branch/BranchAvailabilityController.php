<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Branch\BranchAvailabilityRequest;
use App\Services\EmployeeAvailabilityService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Branch')]
#[Subgroup('Availability', 'View real-time availability of branch employees')]
class BranchAvailabilityController extends Controller
{
    public function __construct(
        private EmployeeAvailabilityService $availabilityService
    ) {
    }

    /**
     * Get availability status for all employees in the authenticated branch.
     * Filter by employee_uuid to get a single employee.
     */
    public function index(BranchAvailabilityRequest $request): JsonResponse
    {
        try {
            $branch = auth('branch')->user();

            $data = $this->availabilityService->getBranchAvailability(
                $branch,
                $request->input('employee_uuid')
            );

            return ApiResponse::success($data);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
