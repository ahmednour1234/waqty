<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\EnforceAuthorization::class,
            \App\Http\Middleware\QueryPerformanceMonitor::class,
        ]);

        $middleware->alias([
            'admin.active' => \App\Http\Middleware\EnsureAdminActive::class,
            'provider.active' => \App\Http\Middleware\EnsureProviderActiveNotBlockedNotBanned::class,
            'employee.active' => \App\Http\Middleware\EnsureEmployeeActiveNotBlocked::class,
            'branch.active'   => \App\Http\Middleware\EnsureBranchActiveNotBlocked::class,
            'detect.language' => \App\Http\Middleware\DetectLanguage::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return \App\Http\Helpers\ApiResponse::error(
                    'api.general.validation_failed',
                    422,
                    $e->errors()
                );
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return \App\Http\Helpers\ApiResponse::error(
                    'api.auth.unauthenticated',
                    401
                );
            }
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return \App\Http\Helpers\ApiResponse::error(
                    'api.general.forbidden',
                    403
                );
            }
        });

        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return \App\Http\Helpers\ApiResponse::error(
                    'api.general.not_found',
                    404
                );
            }
        });

        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return \App\Http\Helpers\ApiResponse::error(
                    'api.rate_limited',
                    429
                );
            }
        });

        if (!app()->environment('production')) {
            $exceptions->render(function (\Exception $e, \Illuminate\Http\Request $request) {
                if ($request->expectsJson()) {
                    return \App\Http\Helpers\ApiResponse::error(
                        $e->getMessage() ?: 'api.general.server_error',
                        500
                    );
                }
            });
        }
    })->create();
