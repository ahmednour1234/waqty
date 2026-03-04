<?php

namespace App\Database\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait QueryPerformance
{
    protected static $queryCount = 0;
    protected static $queryTime = 0.0;
    protected static $slowQueryThreshold = 0.1;

    public static function bootQueryPerformance(): void
    {
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

    public static function getQueryCount(): int
    {
        return self::$queryCount;
    }

    public static function getQueryTime(): float
    {
        return self::$queryTime;
    }

    public static function resetQueryMetrics(): void
    {
        self::$queryCount = 0;
        self::$queryTime = 0.0;
    }

    protected static function validateEagerLoading(Builder $builder): void
    {
        $eagerLoads = $builder->getEagerLoads();

        if (empty($eagerLoads) && $builder->has('*')) {
            Log::warning('Query may benefit from eager loading', [
                'model' => get_class($builder->getModel()),
            ]);
        }
    }
}
