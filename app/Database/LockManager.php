<?php

namespace App\Database;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LockManager
{
    public function acquire(string $key, int $timeout = 10): bool
    {
        $lockKey = "lock:{$key}";
        $acquired = Cache::lock($lockKey, $timeout)->get();

        if (!$acquired) {
            return false;
        }

        return true;
    }

    public function release(string $key): void
    {
        $lockKey = "lock:{$key}";
        Cache::lock($lockKey)->release();
    }

    public function withLock(string $key, int $timeout, callable $callback)
    {
        $lock = Cache::lock("lock:{$key}", $timeout);

        try {
            $lock->block($timeout);
            return $callback();
        } finally {
            $lock->release();
        }
    }

    public function pessimisticLock(string $table, $id, callable $callback)
    {
        return DB::transaction(function () use ($table, $id, $callback) {
            $model = DB::table($table)
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$model) {
                throw new \RuntimeException("Record not found: {$table}#{$id}");
            }

            return $callback($model);
        });
    }

    public function optimisticLock(string $table, $id, int $expectedVersion, callable $callback)
    {
        return DB::transaction(function () use ($table, $id, $expectedVersion, $callback) {
            $model = DB::table($table)
                ->where('id', $id)
                ->where('version', $expectedVersion)
                ->first();

            if (!$model) {
                throw new \RuntimeException("Optimistic lock failed: version mismatch or record not found");
            }

            $result = $callback($model);

            $updated = DB::table($table)
                ->where('id', $id)
                ->where('version', $expectedVersion)
                ->update(['version' => $expectedVersion + 1]);

            if ($updated === 0) {
                throw new \RuntimeException("Optimistic lock failed: concurrent modification detected");
            }

            return $result;
        });
    }
}
