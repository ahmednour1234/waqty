<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Provider\ProviderRevenueRequest;
use App\Services\RevenueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Provider')]
#[Subgroup('Revenue', 'Provider revenue analytics')]
class ProviderRevenueController extends Controller
{
    public function __construct(
        private RevenueService $revenueService
    ) {
    }

    /**
     * Get total revenue across all branches with per-branch and per-employee breakdown.
     * Optionally filter by branch, employee, start_date, end_date.
     */
    public function index(ProviderRevenueRequest $request): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();

            $data = $this->revenueService->getProviderRevenue(
                $provider->id,
                $request->input('branch_uuid'),
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
