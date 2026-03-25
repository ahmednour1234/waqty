<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Provider\BulkAttachServicesRequest;
use App\Http\Requests\Provider\ProviderServiceIndexRequest;
use App\Http\Requests\Provider\StoreServiceRequest;
use App\Http\Requests\Provider\ToggleServiceActiveRequest;
use App\Http\Requests\Provider\UpdateServiceRequest;
use App\Http\Resources\Provider\ProviderServiceResource;
use App\Services\ProviderServiceService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Provider')]
#[Subgroup('Services', 'Provider services CRUD')]
class ProviderServiceController extends Controller
{
    public function __construct(
        private ProviderServiceService $service
    ) {}

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[QueryParam('sub_category_uuid', 'string', 'Filter by subcategory UUID', required: false)]
    #[QueryParam('category', 'string', 'Filter by plain category name (e.g. Hair, Skin)', required: false)]
    #[QueryParam('active', 'boolean', 'Filter by active status', required: false)]
    #[QueryParam('search', 'string', 'Search in service name/description (ar/en)', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    public function index(ProviderServiceIndexRequest $request): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();
            $filters  = $request->only(['sub_category_uuid', 'category', 'active', 'search']);
            if ($request->has('active')) {
                $filters['active'] = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN);
            } else {
                unset($filters['active']);
            }

            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->service->index($provider, $filters, $perPage);

            return ApiResponse::success(
                ProviderServiceResource::collection($paginated->items()),
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
    #[BodyParam('name.ar', 'string', 'Service name in Arabic', required: true)]
    #[BodyParam('name.en', 'string', 'Service name in English', required: true)]
    #[BodyParam('description.ar', 'string', 'Service description in Arabic', required: true)]
    #[BodyParam('description.en', 'string', 'Service description in English', required: true)]
    #[BodyParam('sub_category_uuid', 'string', 'Subcategory UUID', required: true)]
    #[BodyParam('image', 'file', 'Service image (jpeg/png/webp, max 2MB)', required: false)]
    #[BodyParam('active', 'boolean', 'Service active status', required: false)]
    #[Response(['success' => true, 'message' => 'تم الإنشاء بنجاح', 'data' => ['uuid' => '<ULID>']], 201)]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422)]
    public function store(StoreServiceRequest $request): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();
            $service  = $this->service->store($provider, $request->validated(), $request->file('image'));
            return ApiResponse::success(new ProviderServiceResource($service), 'api.services.created', 201);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
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
            $provider = Auth::guard('provider')->user();
            $service  = $this->service->show($provider, $uuid);
            return ApiResponse::success(new ProviderServiceResource($service));
        } catch (AuthorizationException) {
            return ApiResponse::error('api.services.unauthorized', 403);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.services.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('name.ar', 'string', 'Service name in Arabic', required: false)]
    #[BodyParam('name.en', 'string', 'Service name in English', required: false)]
    #[BodyParam('description.ar', 'string', 'Service description in Arabic', required: false)]
    #[BodyParam('description.en', 'string', 'Service description in English', required: false)]
    #[BodyParam('sub_category_uuid', 'string', 'Subcategory UUID', required: false)]
    #[BodyParam('image', 'file', 'Service image (jpeg/png/webp, max 2MB)', required: false)]
    #[BodyParam('active', 'boolean', 'Service active status', required: false)]
    #[Response(['success' => true, 'message' => 'تم التحديث بنجاح', 'data' => ['uuid' => '<ULID>']], 200)]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    public function update(UpdateServiceRequest $request, string $uuid): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();
            $service  = $this->service->update($provider, $uuid, $request->validated(), $request->file('image'));
            return ApiResponse::success(new ProviderServiceResource($service), 'api.general.updated');
        } catch (AuthorizationException) {
            return ApiResponse::error('api.services.unauthorized', 403);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.services.not_found', 404);
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'message' => 'تم الحذف بنجاح'], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();
            $this->service->destroy($provider, $uuid);
            return ApiResponse::success(null, 'api.services.deleted');
        } catch (AuthorizationException) {
            return ApiResponse::error('api.services.unauthorized', 403);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.services.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('active', 'boolean', 'Service active status', required: true)]
    #[Response(['success' => true, 'message' => 'تم التحديث بنجاح', 'data' => ['uuid' => '<ULID>']], 200)]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    public function toggleActive(ToggleServiceActiveRequest $request, string $uuid): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();
            $service  = $this->service->toggleActive($provider, $uuid, $request->boolean('active'));
            return ApiResponse::success(new ProviderServiceResource($service), 'api.general.updated');
        } catch (AuthorizationException) {
            return ApiResponse::error('api.services.unauthorized', 403);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.services.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>']], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    public function assign(string $uuid): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();
            $service  = $this->service->assign($provider, $uuid);
            return ApiResponse::success(new ProviderServiceResource($service), 'api.services.created', 201);
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.services.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('services', 'array', 'List of services to attach or create', required: true)]
    #[BodyParam('services[].uuid', 'string', 'UUID of an existing service to attach (optional)', required: false)]
    #[BodyParam('services[].name_ar', 'string', 'Arabic name for a new service (required if no uuid)', required: false)]
    #[BodyParam('services[].name_en', 'string', 'English name for a new service (required if no uuid)', required: false)]
    #[Response(['success' => true, 'message' => 'تم الإنشاء بنجاح', 'data' => []], 201)]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422)]
    public function bulkAttach(BulkAttachServicesRequest $request): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();
            $attached = $this->service->bulkAttach($provider, $request->input('services'));
            return ApiResponse::success(ProviderServiceResource::collection($attached), 'api.services.created', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
