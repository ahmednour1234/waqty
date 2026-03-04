<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminSubcategoryController;
use App\Http\Controllers\Admin\AdminCountryController;
use App\Http\Controllers\Admin\AdminCityController;
use App\Http\Controllers\Admin\AdminProviderController;
use App\Http\Controllers\Admin\AdminProviderBranchController;
use App\Http\Controllers\Admin\AdminEmployeeController;
use App\Http\Controllers\Provider\ProviderAuthController;
use App\Http\Controllers\Provider\ProviderEmployeeController;
use App\Http\Controllers\Employee\EmployeeAuthController;
use App\Http\Controllers\Employee\EmployeeProfileController;
use App\Http\Controllers\Provider\ProviderProfileController;
use App\Http\Controllers\Provider\ProviderBranchController;
use App\Http\Controllers\Public\PublicCategoryController;
use App\Http\Controllers\Public\PublicSubcategoryController;
use App\Http\Controllers\Public\PublicCountryController;
use App\Http\Controllers\Public\PublicCityController;
use App\Http\Controllers\Public\PublicProviderController;
use App\Http\Controllers\Public\PublicProviderBranchController;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['detect.language'])->group(function () {
Route::prefix('admin')->group(function () {
    Route::prefix('auth')->group(function () {
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
    });
});

Route::prefix('provider/auth')->group(function () {
    Route::post('login', [ProviderAuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('forgot-password', [ProviderAuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('reset-password', [ProviderAuthController::class, 'resetPassword'])->middleware('throttle:5,1');

    Route::middleware(['auth:provider', 'provider.active'])->group(function () {
        Route::post('logout', [ProviderAuthController::class, 'logout']);
        Route::get('me', [ProviderAuthController::class, 'me']);
    });
});

Route::prefix('provider')->middleware(['auth:provider', 'provider.active'])->group(function () {
    Route::put('profile', [ProviderProfileController::class, 'update']);

    Route::get('branches', [ProviderBranchController::class, 'index']);
    Route::post('branches', [ProviderBranchController::class, 'store']);
    Route::get('branches/{branch:uuid}', [ProviderBranchController::class, 'show']);
    Route::put('branches/{branch:uuid}', [ProviderBranchController::class, 'update']);
    Route::delete('branches/{branch:uuid}', [ProviderBranchController::class, 'destroy']);
    Route::patch('branches/{branch:uuid}/main', [ProviderBranchController::class, 'setMain']);

    Route::get('employees', [ProviderEmployeeController::class, 'index']);
    Route::post('employees', [ProviderEmployeeController::class, 'store']);
    Route::get('employees/{employee:uuid}', [ProviderEmployeeController::class, 'show']);
    Route::put('employees/{employee:uuid}', [ProviderEmployeeController::class, 'update']);
    Route::delete('employees/{employee:uuid}', [ProviderEmployeeController::class, 'destroy']);
    Route::patch('employees/{employee:uuid}/active', [ProviderEmployeeController::class, 'toggleActive']);
    Route::patch('employees/{employee:uuid}/block', [ProviderEmployeeController::class, 'block']);
});

Route::prefix('employee/auth')->group(function () {
    Route::post('login', [EmployeeAuthController::class, 'login'])->middleware('throttle:5,1');
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
});

Route::prefix('public')->group(function () {
    Route::get('categories', [PublicCategoryController::class, 'index']);
    Route::get('categories/{category:uuid}', [PublicCategoryController::class, 'show']);
    Route::get('subcategories', [PublicSubcategoryController::class, 'index']);

    Route::get('countries', [PublicCountryController::class, 'index']);
    Route::get('countries/{country:uuid}', [PublicCountryController::class, 'show']);
    Route::get('cities', [PublicCityController::class, 'index']);
    Route::get('cities/{city:uuid}', [PublicCityController::class, 'show']);

    Route::get('providers', [PublicProviderController::class, 'index']);
    Route::get('providers/{provider:uuid}', [PublicProviderController::class, 'show']);

    Route::get('provider-branches', [PublicProviderBranchController::class, 'index']);
    Route::get('provider-branches/{branch:uuid}', [PublicProviderBranchController::class, 'show']);
});

Route::get('images/{type}/{uuid}', [ImageController::class, 'serve'])->name('images.serve');
});
