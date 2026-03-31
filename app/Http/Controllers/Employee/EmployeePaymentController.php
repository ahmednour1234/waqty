<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Employee\EmployeePaymentIndexRequest;
use App\Http\Resources\Employee\EmployeePaymentResource;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Employee')]
#[Subgroup('Payments', 'Employee payment view')]
class EmployeePaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function index(EmployeePaymentIndexRequest $request): JsonResponse
    {
        try {
            $employee  = Auth::guard('employee')->user();
            $filters   = $request->only(['payment_method', 'status', 'booking_uuid', 'from_date', 'to_date']);
            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->paymentService->indexForEmployee($employee, $filters, $perPage);

            return ApiResponse::success(
                EmployeePaymentResource::collection($paginated->items()),
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
            $employee = Auth::guard('employee')->user();
            $payment  = $this->paymentService->showForEmployee($employee, $uuid);

            return ApiResponse::success(new EmployeePaymentResource($payment));
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
