<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;

trait AssertQueryPerformance
{
    protected function assertMaxQueries(int $maxQueries, callable $callback): void
    {
        $queryCount = 0;

        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $callback();

        $this->assertLessThanOrEqual(
            $maxQueries,
            $queryCount,
            "Expected at most {$maxQueries} queries, but {$queryCount} were executed."
        );
    }

    protected function assertQueryTime(float $maxTime, callable $callback): void
    {
        $queryTime = 0.0;

        DB::listen(function ($query) use (&$queryTime) {
            $queryTime += $query->time / 1000;
        });

        $startTime = microtime(true);
        $callback();
        $totalTime = microtime(true) - $startTime;

        $this->assertLessThanOrEqual(
            $maxTime,
            $totalTime,
            "Expected query execution time to be at most {$maxTime}s, but it took {$totalTime}s."
        );
    }

    protected function assertNoNPlusOne(callable $callback): void
    {
        $queries = [];
        $nPlusOneDetected = false;

        DB::listen(function ($query) use (&$queries, &$nPlusOneDetected) {
            $sql = strtolower($query->sql);

            if (str_contains($sql, 'where') && str_contains($sql, 'in (?')) {
                $bindingsCount = count($query->bindings);
                if ($bindingsCount > 10) {
                    $nPlusOneDetected = true;
                }
            }

            $queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ];
        });

        $callback();

        $this->assertFalse(
            $nPlusOneDetected,
            'N+1 query problem detected. Consider using eager loading.'
        );
    }
}
