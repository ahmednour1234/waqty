<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Provider\ProviderAvailabilityRequest;
use App\Services\EmployeeAvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Provider')]
#[Subgroup('Availability', 'View real-time availability of all employees across branches')]
class ProviderAvailabilityController extends Controller
{
    public function __construct(
        private EmployeeAvailabilityService $availabilityService
    ) {
    }

    /**
     * Get availability status for all employees across all branches.
     * Filter by branch_uuid and/or employee_uuid.
     */
    public function index(ProviderAvailabilityRequest $request): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();

            $data = $this->availabilityService->getProviderAvailability(
                $provider->id,
                $request->input('branch_uuid'),
                $request->input('employee_uuid')
            );

            return ApiResponse::success($data);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
