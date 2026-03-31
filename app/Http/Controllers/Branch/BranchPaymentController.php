<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Branch\BranchPaymentIndexRequest;
use App\Http\Requests\Branch\StoreBranchPaymentRequest;
use App\Http\Requests\Branch\UpdateBranchPaymentRequest;
use App\Http\Resources\Provider\ProviderPaymentResource;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Branch')]
#[Subgroup('Payments', 'Branch payment management')]
class BranchPaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function index(BranchPaymentIndexRequest $request): JsonResponse
    {
        try {
            $branch    = auth('branch')->user();
            $filters   = $request->only(['payment_method', 'status', 'booking_uuid', 'from_date', 'to_date']);
            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->paymentService->indexForBranch($branch, $filters, $perPage);

            return ApiResponse::success(
                ProviderPaymentResource::collection($paginated->items()),
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
            $branch  = auth('branch')->user();
            $payment = $this->paymentService->showForBranch($branch, $uuid);

            return ApiResponse::success(new ProviderPaymentResource($payment));
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function store(StoreBranchPaymentRequest $request): JsonResponse
    {
        try {
            $branch  = auth('branch')->user();
            $payment = $this->paymentService->storeForBranch($branch, $request->validated());

            return ApiResponse::success(
                new ProviderPaymentResource($payment->load('booking')),
                'api.payments.created',
                201
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateBranchPaymentRequest $request, string $uuid): JsonResponse
    {
        try {
            $branch  = auth('branch')->user();
            $payment = $this->paymentService->showForBranch($branch, $uuid);
            $payment = $this->paymentService->update($payment, $request->validated());

            return ApiResponse::success(
                new ProviderPaymentResource($payment->load('booking')),
                'api.payments.updated'
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
