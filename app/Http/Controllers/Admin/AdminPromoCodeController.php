<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Admin\AdminPromoCodeStoreRequest;
use App\Http\Requests\Admin\AdminPromoCodeUpdateRequest;
use App\Http\Resources\Admin\AdminPromoCodeResource;
use App\Services\AdminPromoCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Admin')]
#[Subgroup('Promo Codes', 'Discount promo code management')]
class AdminPromoCodeController extends Controller
{
    public function __construct(
        private AdminPromoCodeService $service,
    ) {}

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[QueryParam('search', 'string', 'Search by code', required: false)]
    #[QueryParam('active', 'boolean', 'Filter by active status', required: false)]
    #[QueryParam('type', 'string', 'Filter by type: percentage|fixed', required: false)]
    #[QueryParam('expired', 'boolean', 'true = expired only, false = valid only', required: false)]
    #[QueryParam('trashed', 'string', 'Pass "only" to list soft-deleted promo codes', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page (default 15)', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'search'  => $request->input('search'),
                'active'  => $request->has('active')  ? filter_var($request->input('active'),  FILTER_VALIDATE_BOOLEAN) : null,
                'expired' => $request->has('expired') ? filter_var($request->input('expired'), FILTER_VALIDATE_BOOLEAN) : null,
                'type'    => $request->input('type'),
                'trashed' => $request->input('trashed'),
            ];

            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->service->index($filters, $perPage);

            return ApiResponse::success(
                AdminPromoCodeResource::collection($paginated->items()),
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
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'code' => 'SUMMER50', 'type' => 'percentage', 'value' => 20.0, 'valid_until' => '2026-08-31', 'active' => true]], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function show(string $uuid): JsonResponse
    {
        try {
            $promoCode = $this->service->show($uuid);
            return ApiResponse::success(new AdminPromoCodeResource($promoCode));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Promo code not found.', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[QueryParam('code', 'string', 'Promo code to validate', required: true, example: 'SUMMER50')]
    #[QueryParam('order_amount', 'number', 'Order total to check minimum order requirement', required: false, example: 200)]
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'code' => 'SUMMER50', 'type' => 'percentage', 'value' => 20.0]], 200)]
    #[Response(['success' => false, 'message' => 'This promo code has expired.'], 422)]
    #[Response(['success' => false, 'message' => 'Promo code not found.'], 404)]
    public function validateCode(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        try {
            $promoCode = $this->service->validate(
                $request->input('code'),
                (float) $request->input('order_amount', 0)
            );
            return ApiResponse::success(new AdminPromoCodeResource($promoCode));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Promo code not found.', 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error($e->errors()['code'][0] ?? 'Invalid promo code.', 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('code', 'string', 'Promo code (alphanumeric + dashes, auto-uppercased)', required: true, example: 'SUMMER50')]
    #[BodyParam('type', 'string', 'percentage|fixed (default: percentage)', required: false, example: 'percentage')]
    #[BodyParam('value', 'number', 'Discount value — percentage (0–100) or fixed amount', required: true, example: 20)]
    #[BodyParam('min_order', 'number', 'Minimum order amount in EGP (default: 0)', required: false, example: 100)]
    #[BodyParam('max_discount', 'number', 'Maximum discount cap in EGP (for percentage type)', required: false)]
    #[BodyParam('usage_limit', 'integer', 'Total redemption limit — omit for unlimited', required: false)]
    #[BodyParam('valid_until', 'string', 'Expiry date YYYY-MM-DD (must be today or later)', required: true, example: '2026-08-31')]
    #[BodyParam('active', 'boolean', 'Publish immediately (default: true)', required: false)]
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'code' => 'SUMMER50', 'type' => 'percentage', 'value' => 20.0, 'active' => true]], 201)]
    #[Response(['success' => false, 'message' => 'The given data was invalid.', 'errors' => []], 422)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function store(AdminPromoCodeStoreRequest $request): JsonResponse
    {
        try {
            $admin     = $request->user('admin');
            $promoCode = $this->service->create($request->validated(), $admin->id);
            return ApiResponse::success(new AdminPromoCodeResource($promoCode), null, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation error.', 422, $e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('code', 'string', 'New code (auto-uppercased)', required: false)]
    #[BodyParam('type', 'string', 'percentage|fixed', required: false)]
    #[BodyParam('value', 'number', 'Discount value', required: false)]
    #[BodyParam('min_order', 'number', 'Minimum order (null to clear)', required: false)]
    #[BodyParam('max_discount', 'number', 'Max discount cap (null to clear)', required: false)]
    #[BodyParam('usage_limit', 'integer', 'Usage limit (null = unlimited)', required: false)]
    #[BodyParam('valid_until', 'string', 'New expiry date YYYY-MM-DD', required: false)]
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'code' => 'SUMMER50', 'active' => true]], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function update(AdminPromoCodeUpdateRequest $request, string $uuid): JsonResponse
    {
        try {
            $promoCode = $this->service->update($uuid, $request->validated());
            return ApiResponse::success(new AdminPromoCodeResource($promoCode));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Promo code not found.', 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation error.', 422, $e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('active', 'boolean', 'true = enable, false = disable', required: true, example: false)]
    #[Response(['success' => true, 'data' => ['uuid' => '<ULID>', 'active' => false]], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function setActive(Request $request, string $uuid): JsonResponse
    {
        if (!$request->has('active')) {
            return ApiResponse::error('The active field is required.', 400);
        }

        try {
            $active    = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN);
            $promoCode = $this->service->setActive($uuid, $active);
            return ApiResponse::success(new AdminPromoCodeResource($promoCode));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Promo code not found.', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'message' => 'Promo code deleted successfully.'], 200)]
    #[Response(['success' => false, 'message' => 'غير موجود'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->service->destroy($uuid);
            return ApiResponse::success(null, 'Promo code deleted successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Promo code not found.', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
