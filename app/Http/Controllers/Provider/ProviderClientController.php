<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Resources\Provider\ProviderBookingResource;
use App\Http\Resources\Provider\ProviderClientResource;
use App\Http\Resources\Provider\ProviderClientStatementDetailResource;
use App\Http\Resources\Provider\ProviderClientStatementResource;
use App\Services\ProviderClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Provider')]
#[Subgroup('Clients', 'Clients who have previously booked with this provider')]
class ProviderClientController extends Controller
{
    public function __construct(
        private ProviderClientService $clientService
    ) {}

    /**
     * List all clients who have ever booked with the authenticated provider.
     *
     * @authenticated
     */
    #[QueryParam('search', 'string', 'Search by client name, email, or phone.', required: false)]
    #[QueryParam('branch_uuid', 'string', 'Scope to clients who booked at a specific branch.', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page (default 15).', required: false, example: 15)]
    public function index(Request $request): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();
            $filters  = [
                'search'      => $request->input('search'),
                'branch_uuid' => $request->input('branch_uuid'),
            ];
            $perPage = (int) $request->input('per_page', 15);

            $paginated = $this->clientService->index($provider->id, $filters, $perPage);

            return ApiResponse::success(
                ProviderClientResource::collection($paginated->items()),
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

    /**
     * Get full booking history for a specific client under the authenticated provider.
     *
     * @authenticated
     */
    #[QueryParam('per_page', 'integer', 'Items per page (default 15).', required: false, example: 15)]
    public function bookings(Request $request, string $userUuid): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();
            $perPage  = (int) $request->input('per_page', 15);

            $paginated = $this->clientService->bookingHistory($provider->id, $userUuid, $perPage);

            return ApiResponse::success(
                ProviderBookingResource::collection($paginated->items()),
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error(__('api.clients.not_found'), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * List all clients with financial statement summaries (total charged, paid, outstanding).
     *
     * @authenticated
     */
    #[QueryParam('search', 'string', 'Search by client name, email, or phone.', required: false)]
    #[QueryParam('branch_uuid', 'string', 'Scope to a specific branch.', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page (default 15).', required: false, example: 15)]
    public function statements(Request $request): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();
            $filters  = [
                'search'      => $request->input('search'),
                'branch_uuid' => $request->input('branch_uuid'),
            ];
            $perPage = (int) $request->input('per_page', 15);

            $paginated = $this->clientService->statements($provider->id, $filters, $perPage);

            return ApiResponse::success(
                ProviderClientStatementResource::collection($paginated->items()),
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

    /**
     * Get a detailed financial statement for a single client.
     *
     * @authenticated
     */
    #[QueryParam('per_page', 'integer', 'Bookings per page (default 15).', required: false, example: 15)]
    public function statement(Request $request, string $userUuid): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();
            $perPage  = (int) $request->input('per_page', 15);

            $data = $this->clientService->statement($provider->id, $userUuid, $perPage);

            $bookings = $data['bookings'];

            return ApiResponse::success(
                (new ProviderClientStatementDetailResource($data))->toArray($request),
                null,
                200,
                [
                    'pagination' => [
                        'current_page' => $bookings->currentPage(),
                        'per_page'     => $bookings->perPage(),
                        'total'        => $bookings->total(),
                        'last_page'    => $bookings->lastPage(),
                    ],
                ]
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error(__('api.clients.not_found'), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
