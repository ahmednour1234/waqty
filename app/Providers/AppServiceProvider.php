<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected static $queryCount = 0;
    protected static $queryTime = 0.0;
    protected static $slowQueryThreshold = 0.1;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\AdminRepositoryInterface::class,
            \App\Repositories\AdminRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\CategoryRepositoryInterface::class,
            \App\Repositories\CategoryRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\SubcategoryRepositoryInterface::class,
            \App\Repositories\SubcategoryRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\CountryRepositoryInterface::class,
            \App\Repositories\CountryRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\CityRepositoryInterface::class,
            \App\Repositories\CityRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\GovernorateRepositoryInterface::class,
            \App\Repositories\GovernorateRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\ProviderRepositoryInterface::class,
            \App\Repositories\ProviderRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\ProviderPasswordResetRepositoryInterface::class,
            \App\Repositories\ProviderPasswordResetRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\ProviderBranchRepositoryInterface::class,
            \App\Repositories\ProviderBranchRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\EmployeeRepositoryInterface::class,
            \App\Repositories\EmployeeRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\EmployeePasswordResetRepositoryInterface::class,
            \App\Repositories\EmployeePasswordResetRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\ServiceRepositoryInterface::class,
            \App\Repositories\ServiceRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\ShiftTemplateRepositoryInterface::class,
            \App\Repositories\ShiftTemplateRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\ShiftRepositoryInterface::class,
            \App\Repositories\ShiftRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\ShiftDateRepositoryInterface::class,
            \App\Repositories\ShiftDateRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\ServicePriceRepositoryInterface::class,
            \App\Repositories\ServicePriceRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\PricingGroupRepositoryInterface::class,
            \App\Repositories\PricingGroupRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\PricingGroupEmployeeRepositoryInterface::class,
            \App\Repositories\PricingGroupEmployeeRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\BookingRepositoryInterface::class,
            \App\Repositories\BookingRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\PaymentRepositoryInterface::class,
            \App\Repositories\PaymentRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\AttendanceRepositoryInterface::class,
            \App\Repositories\AttendanceRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\BranchPasswordResetRepositoryInterface::class,
            \App\Repositories\BranchPasswordResetRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\AdminUserRepositoryInterface::class,
            \App\Repositories\AdminUserRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\AdminRatingRepositoryInterface::class,
            \App\Repositories\AdminRatingRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\AdminContentPageRepositoryInterface::class,
            \App\Repositories\AdminContentPageRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\AdminAnnouncementRepositoryInterface::class,
            \App\Repositories\AdminAnnouncementRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (empty(config('jwt.secret'))) {
            $secret = env('JWT_SECRET');
            if (empty($secret)) {
                $secret = app()->environment('testing')
                    ? 'test-secret-key-for-jwt-auth-testing-only-min-32-chars'
                    : 'your-secret-key-change-this-in-production-min-32-characters-long';
            }
            config(['jwt.secret' => $secret]);
        }

        DB::listen(function ($query) {
            self::$queryCount++;
            $executionTime = $query->time / 1000;

            self::$queryTime += $executionTime;

            if ($executionTime > self::$slowQueryThreshold) {
                Log::warning('Slow query detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $executionTime,
                ]);
            }

            if (self::detectNPlusOne($query)) {
                Log::warning('Potential N+1 query detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                ]);
            }
        });
    }

    protected static function detectNPlusOne($query): bool
    {
        $sql = strtolower($query->sql);

        if (str_contains($sql, 'where') && str_contains($sql, 'in (?')) {
            $bindingsCount = count($query->bindings);
            if ($bindingsCount > 10) {
                return true;
            }
        }

        return false;
    }
}
