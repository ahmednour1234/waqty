<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Employee\EmployeeServiceIndexRequest;
use App\Http\Resources\Employee\EmployeeServiceResource;
use App\Http\Resources\Employee\EmployeeServiceWithPriceResource;
use App\Services\EmployeeServiceListingService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Employee')]
#[Subgroup('Services', 'List and show provider services for employee')]
class EmployeeServiceController extends Controller
{
    public function __construct(
        private EmployeeServiceListingService $service
    ) {}

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[QueryParam('sub_category_uuid', 'string', 'Filter by subcategory UUID', required: false)]
    #[QueryParam('category', 'string', 'Filter by plain category name (e.g. Hair, Skin)', required: false)]
    #[QueryParam('search', 'string', 'Search in service name/description (ar/en)', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    public function index(EmployeeServiceIndexRequest $request): JsonResponse
    {
        try {
            $employee  = Auth::guard('employee')->user();
            $filters   = $request->only(['sub_category_uuid', 'category', 'search']);
            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->service->index($employee, $filters, $perPage);

            return ApiResponse::success(
                EmployeeServiceResource::collection($paginated->items()),
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
    #[Header('Authorization', 'Bearer {token}')]
    #[QueryParam('sub_category_uuid', 'string', 'Filter by subcategory UUID', required: false)]
    #[QueryParam('category', 'string', 'Filter by plain category name (e.g. Hair, Skin)', required: false)]
    #[QueryParam('search', 'string', 'Search in service name/description (ar/en)', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    public function indexWithPrices(EmployeeServiceIndexRequest $request): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();
            $filters  = $request->only(['sub_category_uuid', 'category', 'search']);
            $perPage  = (int) $request->input('per_page', 15);

            $result = $this->service->indexWithPrices($employee, $filters, $perPage);

            return ApiResponse::success(
                EmployeeServiceWithPriceResource::collection($result['items']),
                null,
                200,
                [
                    'pagination' => [
                        'current_page' => $result['paginator']->currentPage(),
                        'per_page'     => $result['paginator']->perPage(),
                        'total'        => $result['paginator']->total(),
                        'last_page'    => $result['paginator']->lastPage(),
                    ],
                ]
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>']], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    public function show(string $uuid): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();
            $service  = $this->service->show($employee, $uuid);
            return ApiResponse::success(new EmployeeServiceResource($service));
        } catch (AuthorizationException) {
            return ApiResponse::error('api.services.unauthorized', 403);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.services.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
