<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminSubcategoryController;
use App\Http\Controllers\Api\User\UserAuthController;
use App\Http\Controllers\Admin\AdminCountryController;
use App\Http\Controllers\Admin\AdminCityController;
use App\Http\Controllers\Admin\AdminGovernorateController;
use App\Http\Controllers\Admin\AdminProviderController;
use App\Http\Controllers\Admin\AdminProviderBranchController;
use App\Http\Controllers\Admin\AdminEmployeeController;
use App\Http\Controllers\Admin\AdminServiceController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminRatingController;
use App\Http\Controllers\Provider\ProviderServiceController;
use App\Http\Controllers\Provider\ProviderPaymentController;
use App\Http\Controllers\Employee\EmployeeServiceController;
use App\Http\Controllers\Employee\EmployeePaymentController;
use App\Http\Controllers\Public\PublicServiceController;
use App\Http\Controllers\Provider\ProviderAuthController;
use App\Http\Controllers\Provider\ProviderEmployeeController;
use App\Http\Controllers\Employee\EmployeeAttendanceController;
use App\Http\Controllers\Branch\BranchAvailabilityController;
use App\Http\Controllers\Branch\BranchAuthController;
use App\Http\Controllers\Branch\BranchBookingController;
use App\Http\Controllers\Branch\BranchBookingCountController;
use App\Http\Controllers\Branch\BranchPaymentController;
use App\Http\Controllers\Branch\BranchRevenueController;
use App\Http\Controllers\Employee\EmployeeAvailabilityController;
use App\Http\Controllers\Employee\EmployeeRevenueController;
use App\Http\Controllers\Provider\ProviderAvailabilityController;
use App\Http\Controllers\Provider\ProviderRevenueController;
use App\Http\Controllers\Employee\EmployeeAuthController;
use App\Http\Controllers\Employee\EmployeeProfileController;
use App\Http\Controllers\Provider\ProviderAttendanceController;
use App\Http\Controllers\Provider\ProviderProfileController;
use App\Http\Controllers\Provider\ProviderBranchController;
use App\Http\Controllers\Public\PublicCategoryController;
use App\Http\Controllers\Public\PublicSubcategoryController;
use App\Http\Controllers\Public\PublicCountryController;
use App\Http\Controllers\Public\PublicCityController;
use App\Http\Controllers\Public\PublicGovernorateController;
use App\Http\Controllers\Public\PublicProviderController;
use App\Http\Controllers\Public\PublicEmployeeController;
use App\Http\Controllers\Public\PublicProviderBranchController;
use App\Http\Controllers\Admin\AdminShiftController;
use App\Http\Controllers\Employee\EmployeeShiftController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\Provider\ProviderShiftController;
use App\Http\Controllers\Provider\ProviderShiftTemplateController;
use App\Http\Controllers\Provider\ProviderServicePricingController;
use App\Http\Controllers\Provider\ProviderPricingGroupController;
use App\Http\Controllers\Admin\AdminServicePricingController;
use App\Http\Controllers\Employee\EmployeeServicePricingController;
use App\Http\Controllers\Public\PublicServicePricingController;
use App\Http\Middleware\EnsureUserActiveNotBlockedNotBanned;
use Illuminate\Support\Facades\Route;

Route::middleware(['detect.language'])->group(function () {
Route::prefix('admin')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('send-verification-otp', [AdminAuthController::class, 'sendVerificationOtp'])->middleware('throttle:5,1');
        Route::post('verify-email', [AdminAuthController::class, 'verifyEmail'])->middleware('throttle:5,1');
        Route::post('resend-verification-otp', [AdminAuthController::class, 'resendVerificationOtp'])->middleware('throttle:5,1');
        Route::post('login', [AdminAuthController::class, 'login']);

        Route::middleware(['auth:admin', 'admin.active'])->group(function () {
            Route::post('logout', [AdminAuthController::class, 'logout']);
            Route::get('me', [AdminAuthController::class, 'me']);
        });
    });

    Route::middleware(['auth:admin', 'admin.active'])->group(function () {
        Route::get('admins', [AdminController::class, 'index']);
        Route::post('admins', [AdminController::class, 'store']);
        Route::get('admins/{id}', [AdminController::class, 'show']);
        Route::put('admins/{id}', [AdminController::class, 'update']);
        Route::patch('admins/{id}/active', [AdminController::class, 'toggleActive']);

        Route::get('categories', [AdminCategoryController::class, 'index']);
        Route::post('categories', [AdminCategoryController::class, 'store']);
        Route::get('categories/{category:uuid}', [AdminCategoryController::class, 'show']);
        Route::put('categories/{category:uuid}', [AdminCategoryController::class, 'update']);
        Route::delete('categories/{category:uuid}', [AdminCategoryController::class, 'destroy']);
        Route::patch('categories/{category:uuid}/active', [AdminCategoryController::class, 'toggleActive']);
        Route::post('categories/{category:uuid}/restore', [AdminCategoryController::class, 'restore']);
        Route::delete('categories/{category:uuid}/force', [AdminCategoryController::class, 'forceDelete']);

        Route::get('subcategories', [AdminSubcategoryController::class, 'index']);
        Route::post('subcategories', [AdminSubcategoryController::class, 'store']);
        Route::get('subcategories/{subcategory:uuid}', [AdminSubcategoryController::class, 'show']);
        Route::put('subcategories/{subcategory:uuid}', [AdminSubcategoryController::class, 'update']);
        Route::delete('subcategories/{subcategory:uuid}', [AdminSubcategoryController::class, 'destroy']);
        Route::patch('subcategories/{subcategory:uuid}/active', [AdminSubcategoryController::class, 'toggleActive']);
        Route::post('subcategories/{subcategory:uuid}/restore', [AdminSubcategoryController::class, 'restore']);
        Route::delete('subcategories/{subcategory:uuid}/force', [AdminSubcategoryController::class, 'forceDelete']);

        Route::get('countries', [AdminCountryController::class, 'index']);
        Route::post('countries', [AdminCountryController::class, 'store']);
        Route::get('countries/{country:uuid}', [AdminCountryController::class, 'show']);
        Route::put('countries/{country:uuid}', [AdminCountryController::class, 'update']);
        Route::delete('countries/{country:uuid}', [AdminCountryController::class, 'destroy']);
        Route::patch('countries/{country:uuid}/active', [AdminCountryController::class, 'toggleActive']);
        Route::post('countries/{country:uuid}/restore', [AdminCountryController::class, 'restore']);
        Route::delete('countries/{country:uuid}/force', [AdminCountryController::class, 'forceDelete']);

        Route::get('cities', [AdminCityController::class, 'index']);
        Route::post('cities', [AdminCityController::class, 'store']);
        Route::get('cities/{city:uuid}', [AdminCityController::class, 'show']);
        Route::put('cities/{city:uuid}', [AdminCityController::class, 'update']);
        Route::delete('cities/{city:uuid}', [AdminCityController::class, 'destroy']);
        Route::patch('cities/{city:uuid}/active', [AdminCityController::class, 'toggleActive']);
        Route::post('cities/{city:uuid}/restore', [AdminCityController::class, 'restore']);
        Route::delete('cities/{city:uuid}/force', [AdminCityController::class, 'forceDelete']);

        Route::get('governorates', [AdminGovernorateController::class, 'index']);
        Route::post('governorates', [AdminGovernorateController::class, 'store']);
        Route::get('governorates/{governorate:uuid}', [AdminGovernorateController::class, 'show']);
        Route::put('governorates/{governorate:uuid}', [AdminGovernorateController::class, 'update']);
        Route::delete('governorates/{governorate:uuid}', [AdminGovernorateController::class, 'destroy']);
        Route::patch('governorates/{governorate:uuid}/active', [AdminGovernorateController::class, 'toggleActive']);
        Route::post('governorates/{governorate:uuid}/restore', [AdminGovernorateController::class, 'restore']);
        Route::delete('governorates/{governorate:uuid}/force', [AdminGovernorateController::class, 'forceDelete']);

        Route::get('providers', [AdminProviderController::class, 'index']);
        Route::get('providers/{provider:uuid}', [AdminProviderController::class, 'show']);
        Route::patch('providers/{provider:uuid}/active', [AdminProviderController::class, 'toggleActive']);
        Route::patch('providers/{provider:uuid}/block', [AdminProviderController::class, 'setBlocked']);
        Route::patch('providers/{provider:uuid}/ban', [AdminProviderController::class, 'setBanned']);
        Route::delete('providers/{provider:uuid}', [AdminProviderController::class, 'destroy']);
        Route::post('providers/{provider:uuid}/restore', [AdminProviderController::class, 'restore']);
        Route::delete('providers/{provider:uuid}/force', [AdminProviderController::class, 'forceDelete']);

        Route::get('provider-branches', [AdminProviderBranchController::class, 'index']);
        Route::get('provider-branches/{branch:uuid}', [AdminProviderBranchController::class, 'show']);
        Route::patch('provider-branches/{branch:uuid}/status', [AdminProviderBranchController::class, 'updateStatus']);
        Route::delete('provider-branches/{branch:uuid}', [AdminProviderBranchController::class, 'destroy']);
        Route::post('provider-branches/{branch:uuid}/restore', [AdminProviderBranchController::class, 'restore']);

        Route::get('employees', [AdminEmployeeController::class, 'index']);
        Route::get('employees/{employee:uuid}', [AdminEmployeeController::class, 'show']);
        Route::patch('employees/{employee:uuid}/status', [AdminEmployeeController::class, 'updateStatus']);
        Route::delete('employees/{employee:uuid}', [AdminEmployeeController::class, 'destroy']);
        Route::post('employees/{employee:uuid}/restore', [AdminEmployeeController::class, 'restore']);

        Route::get('services', [AdminServiceController::class, 'index']);
        Route::get('services/{uuid}', [AdminServiceController::class, 'show']);
        Route::patch('services/{uuid}/status', [AdminServiceController::class, 'updateStatus']);
        Route::delete('services/{uuid}', [AdminServiceController::class, 'destroy']);
        Route::post('services/{uuid}/restore', [AdminServiceController::class, 'restore']);

        // Admin shifts
        Route::get('shifts', [AdminShiftController::class, 'index']);
        Route::get('shifts/{uuid}', [AdminShiftController::class, 'show']);

        // Admin shift templates
        Route::get('shift-templates', [AdminShiftController::class, 'indexTemplates']);
        Route::get('shift-templates/{uuid}', [AdminShiftController::class, 'showTemplate']);

        // Admin service pricing (read-only)
        Route::get('service-prices', [AdminServicePricingController::class, 'indexPrices']);
        Route::get('service-prices/{uuid}', [AdminServicePricingController::class, 'showPrice']);
        Route::get('pricing-groups', [AdminServicePricingController::class, 'indexGroups']);
        Route::get('pricing-groups/{uuid}', [AdminServicePricingController::class, 'showGroup']);

        // Admin user management
        Route::get('users', [AdminUserController::class, 'index']);
        Route::get('users/{uuid}', [AdminUserController::class, 'show']);
        Route::patch('users/{uuid}/active', [AdminUserController::class, 'setActive']);
        Route::patch('users/{uuid}/block', [AdminUserController::class, 'setBlocked']);
        Route::patch('users/{uuid}/ban', [AdminUserController::class, 'setBanned']);
        Route::delete('users/{uuid}', [AdminUserController::class, 'destroy']);
        Route::post('users/{uuid}/restore', [AdminUserController::class, 'restore']);

        // Admin ratings moderation
        Route::get('ratings/stats', [AdminRatingController::class, 'stats']);
        Route::get('ratings/analytics', [AdminRatingController::class, 'analytics']);
        Route::get('ratings', [AdminRatingController::class, 'index']);
        Route::get('ratings/{uuid}', [AdminRatingController::class, 'show']);
        Route::patch('ratings/{uuid}/active', [AdminRatingController::class, 'setActive']);
        Route::delete('ratings/{uuid}', [AdminRatingController::class, 'destroy']);
    });
});

Route::prefix('provider/auth')->group(function () {
    Route::post('register', [ProviderAuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('verify-email', [ProviderAuthController::class, 'verifyEmail'])->middleware('throttle:5,1');
    Route::post('resend-verification-otp', [ProviderAuthController::class, 'resendVerificationOtp'])->middleware('throttle:5,1');
    Route::post('login', [ProviderAuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('send-otp', [ProviderAuthController::class, 'sendOtp'])->middleware('throttle:5,1');
    Route::post('verify-otp', [ProviderAuthController::class, 'verifyOtp'])->middleware('throttle:5,1');
    Route::post('reset-password', [ProviderAuthController::class, 'resetPassword'])->middleware('throttle:5,1');

    Route::middleware(['auth:provider', 'provider.active'])->group(function () {
        Route::post('logout', [ProviderAuthController::class, 'logout']);
        Route::get('me', [ProviderAuthController::class, 'me']);
    });
});

Route::prefix('user/auth')->group(function () {
    Route::post('register', [UserAuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('verify-email', [UserAuthController::class, 'verifyEmail'])->middleware('throttle:5,1');
    Route::post('resend-verification-otp', [UserAuthController::class, 'resendVerificationOtp'])->middleware('throttle:5,1');
    Route::post('login', [UserAuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('forgot-password', [UserAuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('verify-otp', [UserAuthController::class, 'verifyOtp'])->middleware('throttle:5,1');
    Route::post('reset-password', [UserAuthController::class, 'resetPassword'])->middleware('throttle:5,1');

    Route::middleware(['auth:user', EnsureUserActiveNotBlockedNotBanned::class])->group(function () {
        Route::post('logout', [UserAuthController::class, 'logout']);
        Route::get('me', [UserAuthController::class, 'me']);
    });
});

Route::prefix('provider')->middleware(['auth:provider', 'provider.active'])->group(function () {
    Route::put('profile', [ProviderProfileController::class, 'update']);

    Route::get('branches', [ProviderBranchController::class, 'index']);
    Route::post('branches', [ProviderBranchController::class, 'store']);
    Route::get('branches/{branch:uuid}', [ProviderBranchController::class, 'show']);
    Route::put('branches/{branch:uuid}', [ProviderBranchController::class, 'update']);
    Route::delete('branches/{branch:uuid}', [ProviderBranchController::class, 'destroy']);
    Route::patch('branches/{branch:uuid}/active', [ProviderBranchController::class, 'toggleActive']);
    Route::patch('branches/{branch:uuid}/main', [ProviderBranchController::class, 'setMain']);

    Route::get('employees', [ProviderEmployeeController::class, 'index']);
    Route::post('employees', [ProviderEmployeeController::class, 'store']);
    Route::get('employees/{employee:uuid}', [ProviderEmployeeController::class, 'show']);
    Route::put('employees/{employee:uuid}', [ProviderEmployeeController::class, 'update']);
    Route::delete('employees/{employee:uuid}', [ProviderEmployeeController::class, 'destroy']);
    Route::patch('employees/{employee:uuid}/active', [ProviderEmployeeController::class, 'toggleActive']);
    Route::patch('employees/{employee:uuid}/block', [ProviderEmployeeController::class, 'block']);
    Route::get('employees/booking-counts', [ProviderEmployeeController::class, 'bookingCounts']);

    Route::get('services', [ProviderServiceController::class, 'index']);
    Route::post('services', [ProviderServiceController::class, 'store']);
    Route::post('services/bulk', [ProviderServiceController::class, 'bulkAttach']);
    Route::post('services/{uuid}/assign', [ProviderServiceController::class, 'assign']);
    Route::get('services/{uuid}', [ProviderServiceController::class, 'show']);
    Route::put('services/{uuid}', [ProviderServiceController::class, 'update']);
    Route::delete('services/{uuid}', [ProviderServiceController::class, 'destroy']);
    Route::patch('services/{uuid}/active', [ProviderServiceController::class, 'toggleActive']);

    // Shift Templates
    Route::get('shift-templates', [ProviderShiftTemplateController::class, 'index']);
    Route::post('shift-templates', [ProviderShiftTemplateController::class, 'store']);
    Route::get('shift-templates/{uuid}', [ProviderShiftTemplateController::class, 'show']);
    Route::put('shift-templates/{uuid}', [ProviderShiftTemplateController::class, 'update']);
    Route::delete('shift-templates/{uuid}', [ProviderShiftTemplateController::class, 'destroy']);
    Route::patch('shift-templates/{uuid}/active', [ProviderShiftTemplateController::class, 'toggleActive']);

    // Shifts
    Route::get('shifts', [ProviderShiftController::class, 'index']);
    Route::post('shifts', [ProviderShiftController::class, 'store']);
    Route::get('shifts/{uuid}', [ProviderShiftController::class, 'show']);
    Route::put('shifts/{uuid}', [ProviderShiftController::class, 'update']);
    Route::delete('shifts/{uuid}', [ProviderShiftController::class, 'destroy']);

    // Service Prices
    Route::get('service-prices', [ProviderServicePricingController::class, 'index']);
    Route::post('service-prices', [ProviderServicePricingController::class, 'store']);
    Route::get('service-prices/{uuid}', [ProviderServicePricingController::class, 'show']);
    Route::put('service-prices/{uuid}', [ProviderServicePricingController::class, 'update']);
    Route::delete('service-prices/{uuid}', [ProviderServicePricingController::class, 'destroy']);
    Route::patch('service-prices/{uuid}/active', [ProviderServicePricingController::class, 'toggleActive']);

    // Pricing Groups
    Route::get('pricing-groups', [ProviderPricingGroupController::class, 'index']);
    Route::post('pricing-groups', [ProviderPricingGroupController::class, 'store']);
    Route::get('pricing-groups/{uuid}', [ProviderPricingGroupController::class, 'show']);
    Route::put('pricing-groups/{uuid}', [ProviderPricingGroupController::class, 'update']);
    Route::delete('pricing-groups/{uuid}', [ProviderPricingGroupController::class, 'destroy']);
    Route::patch('pricing-groups/{uuid}/active', [ProviderPricingGroupController::class, 'toggleActive']);
    Route::put('pricing-groups/{uuid}/employees', [ProviderPricingGroupController::class, 'syncEmployees']);
    Route::post('pricing-groups/{uuid}/employees', [ProviderPricingGroupController::class, 'addEmployees']);
    Route::delete('pricing-groups/{uuid}/employees', [ProviderPricingGroupController::class, 'removeEmployees']);

    // Attendance (read all employees' attendance)
    Route::get('attendance', [ProviderAttendanceController::class, 'index']);

    // Revenue
    Route::get('revenue', [ProviderRevenueController::class, 'index']);

    // Availability
    Route::get('availability', [ProviderAvailabilityController::class, 'index']);
});

Route::prefix('employee/auth')->group(function () {
    Route::post('login', [EmployeeAuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('send-verification-otp', [EmployeeAuthController::class, 'sendVerificationOtp'])->middleware('throttle:5,1');
    Route::post('verify-email', [EmployeeAuthController::class, 'verifyEmail'])->middleware('throttle:5,1');
    Route::post('resend-verification-otp', [EmployeeAuthController::class, 'resendVerificationOtp'])->middleware('throttle:5,1');
    Route::post('send-otp', [EmployeeAuthController::class, 'sendOtp'])->middleware('throttle:5,1');
    Route::post('verify-otp', [EmployeeAuthController::class, 'verifyOtp'])->middleware('throttle:5,1');
    Route::post('forgot-password', [EmployeeAuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('reset-password', [EmployeeAuthController::class, 'resetPassword'])->middleware('throttle:5,1');

    Route::middleware(['auth:employee', 'employee.active'])->group(function () {
        Route::post('logout', [EmployeeAuthController::class, 'logout']);
        Route::get('me', [EmployeeAuthController::class, 'me']);
    });
});

Route::prefix('employee')->middleware(['auth:employee', 'employee.active'])->group(function () {
    Route::put('profile', [EmployeeProfileController::class, 'updateProfile']);
    Route::put('change-password', [EmployeeProfileController::class, 'changePassword']);

    Route::get('services/all', [EmployeeServiceController::class, 'index']);
    Route::get('services', [EmployeeServiceController::class, 'index']);
    Route::get('services/with-prices', [EmployeeServiceController::class, 'indexWithPrices']);
    Route::get('services/{uuid}', [EmployeeServiceController::class, 'show']);

    // Employee shift assignments (read-only)
    Route::get('shifts', [EmployeeShiftController::class, 'index']);
    Route::get('shifts/{uuid}', [EmployeeShiftController::class, 'show']);

    // Employee service pricing (resolved price only)
    Route::get('service-pricing/services/{uuid}/price', [EmployeeServicePricingController::class, 'resolvePrice']);

    // Attendance
    Route::post('attendance/check-in', [EmployeeAttendanceController::class, 'checkIn']);
    Route::post('attendance/check-out', [EmployeeAttendanceController::class, 'checkOut']);
    Route::get('attendance', [EmployeeAttendanceController::class, 'index']);

    // Revenue
    Route::get('revenue', [EmployeeRevenueController::class, 'index']);

    // Availability
    Route::get('availability', [EmployeeAvailabilityController::class, 'show']);
    Route::patch('availability', [EmployeeAvailabilityController::class, 'update']);
    Route::post('bookings/{booking_uuid}/session/start', [EmployeeAvailabilityController::class, 'startSession']);
    Route::post('bookings/{booking_uuid}/session/end', [EmployeeAvailabilityController::class, 'endSession']);
});

Route::prefix('public')->group(function () {
    Route::get('categories', [PublicCategoryController::class, 'index']);
    Route::get('categories/{category:uuid}', [PublicCategoryController::class, 'show']);
    Route::get('subcategories', [PublicSubcategoryController::class, 'index']);

    Route::get('countries', [PublicCountryController::class, 'index']);
    Route::get('countries/{country:uuid}', [PublicCountryController::class, 'show']);
    Route::get('cities', [PublicCityController::class, 'index']);
    Route::get('cities/{city:uuid}', [PublicCityController::class, 'show']);
    Route::get('governorates', [PublicGovernorateController::class, 'index']);
    Route::get('governorates/{governorate:uuid}', [PublicGovernorateController::class, 'show']);

    Route::get('providers', [PublicProviderController::class, 'index']);
    Route::get('providers/{provider:uuid}', [PublicProviderController::class, 'show']);

    Route::get('provider-branches', [PublicProviderBranchController::class, 'index']);
    Route::get('provider-branches/{branch:uuid}', [PublicProviderBranchController::class, 'show']);

    Route::get('employees', [PublicEmployeeController::class, 'index']);

    Route::get('services/all', [PublicServiceController::class, 'index']);
    Route::get('services', [PublicServiceController::class, 'index']);
    Route::get('services/newest', [PublicServiceController::class, 'newest']);
    Route::get('services/nearest', [PublicServiceController::class, 'nearest']);
    Route::get('services/{uuid}', [PublicServiceController::class, 'show']);

    // Public service pricing (resolved price only)
    Route::get('service-pricing/services/{uuid}/price', [PublicServicePricingController::class, 'resolvePrice']);
});

Route::get('images/{type}/{uuid}', [ImageController::class, 'serve'])->name('images.serve');

// ─── Booking Availability (public, no auth) ─────────────────────────────────
Route::prefix('public/bookings')->group(function () {
    Route::get('available-dates', [\App\Http\Controllers\Public\PublicBookingAvailabilityController::class, 'availableDates']);
    Route::get('available-slots', [\App\Http\Controllers\Public\PublicBookingAvailabilityController::class, 'availableSlots']);
});

// ─── User Bookings ───────────────────────────────────────────────────────────
Route::prefix('user/bookings')->middleware(['auth:user', \App\Http\Middleware\EnsureUserActiveNotBlockedNotBanned::class])->group(function () {
    Route::get('/', [\App\Http\Controllers\User\UserBookingController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\User\UserBookingController::class, 'store']);
    Route::get('{uuid}', [\App\Http\Controllers\User\UserBookingController::class, 'show']);
    Route::patch('{uuid}/cancel', [\App\Http\Controllers\User\UserBookingController::class, 'cancel']);
    Route::post('{uuid}/rate', [\App\Http\Controllers\User\UserBookingRatingController::class, 'store']);
});

// ─── User Payments ───────────────────────────────────────────────────────────
Route::prefix('user/payments')->middleware(['auth:user', \App\Http\Middleware\EnsureUserActiveNotBlockedNotBanned::class])->group(function () {
    Route::get('/', [\App\Http\Controllers\User\UserPaymentController::class, 'index']);
    Route::get('{uuid}', [\App\Http\Controllers\User\UserPaymentController::class, 'show']);
});

// ─── Provider Bookings ───────────────────────────────────────────────────────
Route::prefix('provider/bookings')->middleware(['auth:provider', 'provider.active'])->group(function () {
    Route::post('/', [\App\Http\Controllers\Provider\ProviderBookingController::class, 'store']);
    Route::get('/', [\App\Http\Controllers\Provider\ProviderBookingController::class, 'index']);
    Route::get('grid', [\App\Http\Controllers\Provider\ProviderBookingController::class, 'grid']);
    Route::get('next-upcoming', [\App\Http\Controllers\Provider\ProviderBookingController::class, 'nextUpcoming']);
    Route::get('{uuid}', [\App\Http\Controllers\Provider\ProviderBookingController::class, 'show']);
    Route::patch('{uuid}/status', [\App\Http\Controllers\Provider\ProviderBookingController::class, 'updateStatus']);
});

// ─── Provider Payments ───────────────────────────────────────────────────────
Route::prefix('provider/payments')->middleware(['auth:provider', 'provider.active'])->group(function () {
    Route::get('/', [ProviderPaymentController::class, 'index']);
    Route::post('/', [ProviderPaymentController::class, 'store']);
    Route::get('{uuid}', [ProviderPaymentController::class, 'show']);
    Route::put('{uuid}', [ProviderPaymentController::class, 'update']);
});

Route::prefix('provider/ratings')->middleware(['auth:provider', 'provider.active'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Provider\ProviderRatingController::class, 'index']);
});

// ─── Employee Bookings ───────────────────────────────────────────────────────
Route::prefix('employee/bookings')->middleware(['auth:employee', 'employee.active'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Employee\EmployeeBookingController::class, 'index']);
    Route::get('next-upcoming', [\App\Http\Controllers\Employee\EmployeeBookingController::class, 'nextUpcoming']);
    Route::get('{uuid}', [\App\Http\Controllers\Employee\EmployeeBookingController::class, 'show']);
    Route::patch('{uuid}/status', [\App\Http\Controllers\Employee\EmployeeBookingController::class, 'updateStatus']);
});

// ─── Employee Payments ───────────────────────────────────────────────────────
Route::prefix('employee/payments')->middleware(['auth:employee', 'employee.active'])->group(function () {
    Route::get('/', [EmployeePaymentController::class, 'index']);
    Route::get('{uuid}', [EmployeePaymentController::class, 'show']);
});

Route::prefix('employee/ratings')->middleware(['auth:employee', 'employee.active'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Employee\EmployeeRatingController::class, 'index']);
});

// ─── Admin Bookings ──────────────────────────────────────────────────────────
Route::prefix('admin/bookings')->middleware(['auth:admin', 'admin.active'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminBookingController::class, 'index']);
    Route::get('next-upcoming', [\App\Http\Controllers\Admin\AdminBookingController::class, 'nextUpcoming']);
    Route::get('{uuid}', [\App\Http\Controllers\Admin\AdminBookingController::class, 'show']);
    Route::patch('{uuid}/status', [\App\Http\Controllers\Admin\AdminBookingController::class, 'updateStatus']);
    Route::delete('{uuid}', [\App\Http\Controllers\Admin\AdminBookingController::class, 'destroy']);
});

// ─── Admin Payments ──────────────────────────────────────────────────────────
Route::prefix('admin/payments')->middleware(['auth:admin', 'admin.active'])->group(function () {
    Route::get('/', [AdminPaymentController::class, 'index']);
    Route::get('{uuid}', [AdminPaymentController::class, 'show']);
    Route::put('{uuid}', [AdminPaymentController::class, 'update']);
    Route::delete('{uuid}', [AdminPaymentController::class, 'destroy']);
});

// ─── Branch Auth ─────────────────────────────────────────────────────────────
Route::prefix('branch/auth')->group(function () {
    Route::post('login', [BranchAuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('forgot-password', [BranchAuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('verify-otp', [BranchAuthController::class, 'verifyOtp'])->middleware('throttle:5,1');
    Route::post('reset-password', [BranchAuthController::class, 'resetPassword'])->middleware('throttle:5,1');

    Route::middleware(['auth:branch', 'branch.active'])->group(function () {
        Route::post('logout', [BranchAuthController::class, 'logout']);
        Route::get('me', [BranchAuthController::class, 'me']);
    });
});

    Route::prefix('branch')->middleware(['auth:branch', 'branch.active'])->group(function () {
        Route::get('employees/booking-counts', [BranchBookingCountController::class, 'employeeBookingCounts']);
        Route::get('revenue', [BranchRevenueController::class, 'index']);
        Route::get('availability', [BranchAvailabilityController::class, 'index']);
        Route::get('bookings', [BranchBookingController::class, 'index']);
        Route::post('bookings', [BranchBookingController::class, 'store']);
        Route::get('bookings/grid', [BranchBookingController::class, 'grid']);
        Route::get('bookings/next-upcoming', [BranchBookingController::class, 'nextUpcoming']);
        Route::get('bookings/{uuid}', [BranchBookingController::class, 'show']);
        Route::get('payments', [BranchPaymentController::class, 'index']);
        Route::post('payments', [BranchPaymentController::class, 'store']);
        Route::get('payments/{uuid}', [BranchPaymentController::class, 'show']);
        Route::put('payments/{uuid}', [BranchPaymentController::class, 'update']);
    });
});
