<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\User\StoreBookingRatingRequest;
use App\Http\Resources\BookingRatingResource;
use App\Services\BookingRatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('User')]
#[Subgroup('Booking Ratings', 'User rates completed bookings')]
class UserBookingRatingController extends Controller
{
    public function __construct(
        private BookingRatingService $ratingService
    ) {}

    /**
     * Rate a completed booking.
     *
     * Creates or updates the booking rating for the authenticated user.
     *
     * @authenticated
     */
    #[BodyParam('rating', 'integer', 'Star rating value from 1 to 5.', required: true, example: 5)]
    #[BodyParam('comment', 'string', 'Optional feedback comment.', required: false, example: 'Great service and very professional.')]
    #[BodyParam('active', 'boolean', 'Optional flag to show/hide this rating.', required: false, example: true)]
    public function store(StoreBookingRatingRequest $request, string $uuid): JsonResponse
    {
        try {
            $user = Auth::guard('user')->user();
            $rating = $this->ratingService->rateBooking($user, $uuid, $request->validated());

            return ApiResponse::success(
                new BookingRatingResource($rating->load(['booking', 'user'])),
                null,
                201
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
