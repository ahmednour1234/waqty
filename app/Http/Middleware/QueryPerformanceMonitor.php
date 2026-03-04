<?php

namespace App\Http\Middleware;

use App\Exceptions\PerformanceViolationException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class QueryPerformanceMonitor
{
    protected $maxQueries = 50;
    protected $maxQueryTime = 1.0;

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('up') || $request->is('health')) {
            return $next($request);
        }

        $queryCount = 0;
        $queryTime = 0.0;

        DB::listen(function ($query) use (&$queryCount, &$queryTime) {
            $queryCount++;
            $queryTime += $query->time / 1000;
        });

        $startTime = microtime(true);
        $response = $next($request);
        $totalTime = microtime(true) - $startTime;

        if ($queryCount > $this->maxQueries) {
            Log::error('Query count threshold exceeded', [
                'count' => $queryCount,
                'max' => $this->maxQueries,
                'route' => $request->route()?->getName(),
            ]);

            if (config('app.debug')) {
                throw new PerformanceViolationException(
                    "Query count exceeded: {$queryCount} queries (max: {$this->maxQueries})"
                );
            }
        }

        if ($queryTime > $this->maxQueryTime) {
            Log::error('Query time threshold exceeded', [
                'time' => $queryTime,
                'max' => $this->maxQueryTime,
                'route' => $request->route()?->getName(),
            ]);

            if (config('app.debug')) {
                throw new PerformanceViolationException(
                    "Query time exceeded: {$queryTime}s (max: {$this->maxQueryTime}s)"
                );
            }
        }

        return $response;
    }
}
