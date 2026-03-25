<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Employee\EmployeeRatingIndexRequest;
use App\Http\Resources\BookingRatingResource;
use App\Services\BookingRatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Employee')]
#[Subgroup('Ratings', 'Employee reviews and ratings')]
class EmployeeRatingController extends Controller
{
    public function __construct(
        private BookingRatingService $ratingService
    ) {}

    /**
     * List ratings for authenticated employee bookings.
     *
     * @authenticated
     */
    #[QueryParam('booking_uuid', 'string', 'Filter by booking UUID.', required: false)]
    #[QueryParam('from_date', 'string', 'Filter ratings from date (Y-m-d).', required: false, example: '2026-01-01')]
    #[QueryParam('to_date', 'string', 'Filter ratings to date (Y-m-d).', required: false, example: '2026-12-31')]
    #[QueryParam('rating', 'integer', 'Filter by exact star value (1-5).', required: false, example: 5)]
    #[QueryParam('active', 'boolean', 'Filter by rating active status.', required: false, example: true)]
    #[QueryParam('per_page', 'integer', 'Items per page.', required: false, example: 15)]
    public function index(EmployeeRatingIndexRequest $request): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();
            $filters = $request->only(['booking_uuid', 'from_date', 'to_date', 'rating', 'active']);
            $perPage = (int) $request->input('per_page', 15);

            $paginated = $this->ratingService->employeeRatings($employee, $filters, $perPage);

            return ApiResponse::success(
                BookingRatingResource::collection($paginated->items()),
                null,
                200,
                [
                    'pagination' => [
                        'current_page' => $paginated->currentPage(),
                        'per_page' => $paginated->perPage(),
                        'total' => $paginated->total(),
                        'last_page' => $paginated->lastPage(),
                    ],
                ]
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
