<?php

namespace App\Database\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait TransactionalOperations
{
    protected function executeInTransaction(callable $callback, int $maxRetries = 3)
    {
        $attempts = 0;

        while ($attempts < $maxRetries) {
            try {
                return DB::transaction(function () use ($callback) {
                    return $callback();
                });
            } catch (\Illuminate\Database\QueryException $e) {
                $attempts++;

                if ($this->isDeadlock($e) && $attempts < $maxRetries) {
                    Log::warning('Deadlock detected, retrying transaction', [
                        'attempt' => $attempts,
                        'error' => $e->getMessage(),
                    ]);

                    usleep(100000 * $attempts);
                    continue;
                }

                throw $e;
            }
        }
    }

    protected function isDeadlock(\Exception $e): bool
    {
        $message = $e->getMessage();
        return str_contains($message, 'Deadlock') ||
               str_contains($message, 'deadlock') ||
               str_contains($message, '1213') ||
               str_contains($message, '40001');
    }

    protected function withLock(string $table, $id, callable $callback)
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
}
