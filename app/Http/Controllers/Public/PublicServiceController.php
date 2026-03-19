<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Public\PublicServiceIndexRequest;
use App\Http\Resources\Public\PublicServiceResource;
use App\Services\PublicServiceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Public')]
#[Subgroup('Services', 'List and show active services')]
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
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>']], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    public function show(string $uuid): JsonResponse
    {
        try {
            $service = $this->service->show($uuid);
            return ApiResponse::success(new PublicServiceResource($service));
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.services.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
