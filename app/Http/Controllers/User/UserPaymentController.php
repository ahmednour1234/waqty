<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\User\UserPaymentIndexRequest;
use App\Http\Resources\User\UserPaymentResource;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('User')]
#[Subgroup('Payments', 'User payment history')]
class UserPaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function index(UserPaymentIndexRequest $request): JsonResponse
    {
        try {
            $user      = Auth::guard('user')->user();
            $filters   = $request->only(['payment_method', 'status', 'booking_uuid', 'from_date', 'to_date']);
            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->paymentService->indexForUser($user, $filters, $perPage);

            return ApiResponse::success(
                UserPaymentResource::collection($paginated->items()),
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
            $user    = Auth::guard('user')->user();
            $payment = $this->paymentService->showForUser($user, $uuid);

            return ApiResponse::success(new UserPaymentResource($payment));
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
