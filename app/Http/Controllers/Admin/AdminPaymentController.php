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
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Admin')]
#[Subgroup('Payments', 'Admin payment management')]
class AdminPaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

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
