<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Provider\ProviderPaymentIndexRequest;
use App\Http\Requests\Provider\StorePaymentRequest;
use App\Http\Requests\Provider\UpdatePaymentRequest;
use App\Http\Resources\Provider\ProviderPaymentResource;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Provider')]
#[Subgroup('Payments', 'Provider payment management')]
class ProviderPaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function index(ProviderPaymentIndexRequest $request): JsonResponse
    {
        try {
            $provider  = Auth::guard('provider')->user();
            $filters   = $request->only(['payment_method', 'status', 'booking_uuid', 'from_date', 'to_date']);
            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->paymentService->indexForProvider($provider, $filters, $perPage);

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
            $provider = Auth::guard('provider')->user();
            $payment  = $this->paymentService->showForProvider($provider, $uuid);

            return ApiResponse::success(new ProviderPaymentResource($payment));
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function store(StorePaymentRequest $request): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();
            $payment  = $this->paymentService->storeForProvider($provider, $request->validated());

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

    public function update(UpdatePaymentRequest $request, string $uuid): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();
            $payment  = $this->paymentService->showForProvider($provider, $uuid);
            $payment  = $this->paymentService->update($payment, $request->validated());

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
