<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Services\ProviderDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Provider')]
#[Subgroup('Dashboard', 'Provider dashboard statistics')]
class ProviderDashboardController extends Controller
{
    public function __construct(
        private ProviderDashboardService $dashboardService
    ) {}

    /**
     * Get dashboard statistics for the authenticated provider.
     *
     * Returns aggregated counts and metrics across bookings, revenue, employees,
     * branches, ratings, and payments.
     *
     * @authenticated
     */
    public function index(): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();
            $stats    = $this->dashboardService->getStats($provider->id);

            return ApiResponse::success($stats);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
