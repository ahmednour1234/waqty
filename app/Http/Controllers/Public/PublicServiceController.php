<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Public\PublicServiceIndexRequest;
use App\Http\Resources\Public\PublicServiceDetailResource;
use App\Http\Resources\Public\PublicServiceResource;
use App\Services\PublicServiceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Public')]
#[Subgroup('Services', 'List and show services')]
class PublicServiceController extends Controller
{
    public function __construct(
        private PublicServiceService $service
    ) {}

    #[Header('Accept-Language', 'ar|en')]
    #[QueryParam('provider_uuid', 'string', 'Filter by provider UUID', required: false)]
    #[QueryParam('sub_category_uuid', 'string', 'Filter by subcategory UUID', required: false)]
    #[QueryParam('search', 'string', 'Search in service name/description (ar/en)', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    public function index(PublicServiceIndexRequest $request): JsonResponse
    {
        try {
            $filters   = $request->only(['provider_uuid', 'sub_category_uuid', 'search']);
            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->service->index($filters, $perPage);

            return ApiResponse::success(
                PublicServiceResource::collection($paginated->items()),
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

    #[Header('Accept-Language', 'ar|en')]
    #[QueryParam('per_page', 'integer', 'Items per page', required: false, example: 10)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => []]], 200)]
    public function newest(Request $request): JsonResponse
    {
        try {
            $perPage   = (int) $request->input('per_page', 10);
            $paginated = $this->service->newest($perPage);

            return ApiResponse::success(
                PublicServiceResource::collection($paginated->items()),
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

    #[Header('Accept-Language', 'ar|en')]
    #[QueryParam('lat', 'number', 'Latitude of the user location', required: false, example: 24.7136)]
    #[QueryParam('lng', 'number', 'Longitude of the user location', required: false, example: 46.6753)]
    #[QueryParam('radius', 'number', 'Search radius in kilometres (default 50)', required: false, example: 50)]
    #[QueryParam('per_page', 'integer', 'Items per page', required: false, example: 10)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => []]], 200)]
    public function nearest(Request $request): JsonResponse
    {
        try {
            $lat     = $request->input('lat');
            $lng     = $request->input('lng');
            $radius  = (float) $request->input('radius', 50);
            $perPage = (int) $request->input('per_page', 10);

            $fallback  = ($lat === null || $lng === null);
            $paginated = $this->service->nearest(
                (float) $lat,
                (float) $lng,
                $radius,
                $perPage,
                $fallback
            );

            return ApiResponse::success(
                PublicServiceResource::collection($paginated->items()),
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

    #[Header('Accept-Language', 'ar|en')]
    #[QueryParam('provider_uuid', 'string', 'Provider UUID to load branches', required: false)]
    #[QueryParam('branch_uuid', 'string', 'Branch UUID to load employees (requires provider_uuid)', required: false)]
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>']], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    public function show(Request $request, string $uuid): JsonResponse
    {
        try {
            $providerUuid = $request->input('provider_uuid');
            $branchUuid   = $request->input('branch_uuid');
            $detail       = $this->service->showDetail($uuid, $providerUuid, $branchUuid);

            return ApiResponse::success(new PublicServiceDetailResource($detail));
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
