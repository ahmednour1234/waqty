<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Resources\Provider\ProviderAttendanceResource;
use App\Services\ProviderAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Provider')]
#[Subgroup('Attendance', 'View attendance records of all employees in this provider')]
#[Header('Authorization', 'Bearer {token}')]
class ProviderAttendanceController extends Controller
{
    public function __construct(private ProviderAttendanceService $service) {}

    /**
     * List employees' attendance.
     *
     * Returns paginated attendance records for all employees belonging to the
     * authenticated provider. Optionally filter by employee, date range.
     */
    #[QueryParam('employee_uuid', 'string', 'Filter by employee UUID (must belong to this provider).', required: false)]
    #[QueryParam('date_from', 'string', 'Filter from date (Y-m-d).', required: false, example: '2026-03-01')]
    #[QueryParam('date_to', 'string', 'Filter to date (Y-m-d).', required: false, example: '2026-03-31')]
    #[QueryParam('per_page', 'integer', 'Items per page.', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = array_filter([
                'employee_uuid' => $request->input('employee_uuid'),
                'date_from'     => $request->input('date_from'),
                'date_to'       => $request->input('date_to'),
            ], fn($v) => $v !== null);

            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->service->index($filters, $perPage);

            return ApiResponse::success(
                ProviderAttendanceResource::collection($paginated->items()),
                null,
                200,
                ['pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'per_page'     => $paginated->perPage(),
                    'total'        => $paginated->total(),
                    'last_page'    => $paginated->lastPage(),
                ]]
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
