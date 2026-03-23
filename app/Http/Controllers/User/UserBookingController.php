<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\User\CancelBookingRequest;
use App\Http\Requests\User\StoreBookingRequest;
use App\Http\Requests\User\UserBookingIndexRequest;
use App\Http\Resources\User\UserBookingResource;
use App\Services\BookingCreationService;
use App\Services\UserBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('User')]
#[Subgroup('Bookings', 'User booking management')]
class UserBookingController extends Controller
{
    public function __construct(
        private UserBookingService $bookingService,
        private BookingCreationService $creationService
    ) {}

    public function index(UserBookingIndexRequest $request): JsonResponse
    {
        try {
            $user    = Auth::guard('user')->user();
            $filters = $request->only(['status', 'from_date', 'to_date', 'upcoming', 'past', 'booking_date']);

            if ($request->boolean('upcoming')) {
                $filters['upcoming'] = true;
            }
            if ($request->boolean('past')) {
                $filters['past'] = true;
            }

            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->bookingService->index($user, $filters, $perPage);

            return ApiResponse::success(
                UserBookingResource::collection($paginated->items()),
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

    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            $user    = Auth::guard('user')->user();
            $booking = $this->creationService->create($user, $request->validated());

            return ApiResponse::success(
                new UserBookingResource($booking->load(['provider', 'branch', 'employee', 'service'])),
                'api.bookings.created',
                201
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function show(string $uuid): JsonResponse
    {
        try {
            $user    = Auth::guard('user')->user();
            $booking = $this->bookingService->show($user, $uuid);

            return ApiResponse::success(new UserBookingResource($booking));
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function cancel(CancelBookingRequest $request, string $uuid): JsonResponse
    {
        try {
            $user    = Auth::guard('user')->user();
            $booking = $this->bookingService->cancel($user, $uuid, $request->input('cancellation_reason'));

            return ApiResponse::success(
                new UserBookingResource($booking),
                'api.bookings.cancelled'
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
