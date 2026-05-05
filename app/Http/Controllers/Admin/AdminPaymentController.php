<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Admin\AdminPaymentIndexRequest;
use App\Http\Requests\Admin\AdminUpdatePaymentRequest;
use App\Http\Resources\Admin\AdminPaymentResource;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Admin')]
#[Subgroup('Payments', 'Admin payment management')]
class AdminPaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[QueryParam('payment_method', 'string', 'Filter by payment method (cash, paymob).', required: false, example: 'cash')]
    #[QueryParam('status', 'string', 'Filter by status (pending, completed, failed, refunded).', required: false, example: 'completed')]
    #[QueryParam('booking_uuid', 'string', 'Filter by booking UUID.', required: false)]
    #[QueryParam('provider_uuid', 'string', 'Filter by provider UUID.', required: false)]
    #[QueryParam('from_date', 'string', 'Start date filter (Y-m-d).', required: false, example: '2026-01-01')]
    #[QueryParam('to_date', 'string', 'End date filter (Y-m-d).', required: false, example: '2026-12-31')]
    #[QueryParam('trashed', 'string', 'Include soft-deleted records. Values: only, with.', required: false)]
    #[QueryParam('per_page', 'integer', 'Items per page (default 15).', required: false, example: 15)]
    #[Response(['success' => true, 'data' => [], 'meta' => ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]], 200)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط'], 403)]
    public function index(AdminPaymentIndexRequest $request): JsonResponse
    {
        try {
            $filters   = $request->only(['payment_method', 'status', 'booking_uuid', 'provider_uuid', 'from_date', 'to_date', 'trashed']);
            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->paymentService->indexAdmin($filters, $perPage);

            return ApiResponse::success(
                AdminPaymentResource::collection($paginated->items()),
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
    #[Response(['success' => true, 'data' => ['uuid' => '<UUID>', 'amount' => 150.0, 'status' => 'completed', 'payment_method' => 'cash']], 200)]
    #[Response(['success' => false, 'message' => 'Not found'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function show(string $uuid): JsonResponse
    {
        try {
            $payment = $this->paymentService->showAdmin($uuid);

            return ApiResponse::success(new AdminPaymentResource($payment));
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[BodyParam('payment_method', 'string', 'Payment method: cash or paymob.', required: false, example: 'paymob')]
    #[BodyParam('amount', 'number', 'Payment amount.', required: false, example: 200.0)]
    #[BodyParam('status', 'string', 'Payment status: pending, completed, failed, or refunded.', required: false, example: 'completed')]
    #[BodyParam('transaction_id', 'string', 'External transaction reference.', required: false, example: 'TXN-123456')]
    #[BodyParam('notes', 'string', 'Optional notes.', required: false)]
    #[Response(['success' => true, 'message' => 'api.payments.updated', 'data' => ['uuid' => '<UUID>', 'status' => 'completed']], 200)]
    #[Response(['success' => false, 'message' => 'Not found'], 404)]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function update(AdminUpdatePaymentRequest $request, string $uuid): JsonResponse
    {
        try {
            $payment = $this->paymentService->showAdmin($uuid);
            $payment = $this->paymentService->update($payment, $request->validated());

            return ApiResponse::success(
                new AdminPaymentResource($payment->load('booking')),
                'api.payments.updated'
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Response(['success' => true, 'message' => 'api.payments.deleted'], 200)]
    #[Response(['success' => false, 'message' => 'Not found'], 404)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $payment = $this->paymentService->showAdmin($uuid);
            $this->paymentService->destroy($payment);

            return ApiResponse::success(null, 'api.payments.deleted');
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
