<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Resources\Public\PublicEmployeeResource;
use App\Models\Employee;
use App\Models\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Public')]
#[Subgroup('Employees', 'List active employees with their assigned services')]
class PublicEmployeeController extends Controller
{
    #[Header('Accept-Language', 'ar|en')]
    #[QueryParam('provider_uuid', 'string', 'Filter by provider UUID', required: false)]
    #[QueryParam('search', 'string', 'Search in employee name', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page (default 15)', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage     = (int) $request->input('per_page', 15);
            $search      = $request->input('search');
            $providerUuid = $request->input('provider_uuid');

            $query = Employee::with(['provider', 'branch', 'assignedServicePrices'])
                ->where('active', true)
                ->where('blocked', false)
                ->whereNull('deleted_at')
                // only employees who have at least one active employee-scoped service price
                ->whereHas('assignedServicePrices');

            if ($providerUuid !== null) {
                $provider = Provider::whereUuid($providerUuid)
                    ->where('active', true)
                    ->where('blocked', false)
                    ->where('banned', false)
                    ->whereNull('deleted_at')
                    ->first();

                if (!$provider) {
                    return ApiResponse::success([], null, 200, [
                        'pagination' => [
                            'current_page' => 1,
                            'per_page'     => $perPage,
                            'total'        => 0,
                            'last_page'    => 1,
                        ],
                    ]);
                }

                $query->where('provider_id', $provider->id);
            }

            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }

            $paginated = $query->orderBy('name')->paginate($perPage);

            return ApiResponse::success(
                PublicEmployeeResource::collection($paginated->items()),
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
}
