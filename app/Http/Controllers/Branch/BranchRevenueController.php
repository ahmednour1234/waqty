<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Branch\BranchRevenueRequest;
use App\Services\RevenueService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Branch')]
#[Subgroup('Revenue', 'Branch revenue analytics')]
class BranchRevenueController extends Controller
{
    public function __construct(
        private RevenueService $revenueService
    ) {
    }

    /**
     * Get total revenue for the authenticated branch with per-employee breakdown.
     * Optionally filter by employee, start_date, end_date.
     */
    public function index(BranchRevenueRequest $request): JsonResponse
    {
        try {
            $branch = auth('branch')->user();

            $data = $this->revenueService->getBranchRevenue(
                $branch,
                $request->input('employee_uuid'),
                $request->input('start_date'),
                $request->input('end_date')
            );

            return ApiResponse::success($data);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
