<?php

namespace App\Jobs;

use App\Database\Concerns\TransactionalOperations;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TransactionalOperations;

    public $tries = 3;
    public $timeout = 120;

    public function handle(): void
    {
        try {
            $this->executeInTransaction(function () {
                $this->execute();
            });
        } catch (\Exception $e) {
            Log::error('Job execution failed', [
                'job' => get_class($this),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    abstract protected function execute(): void;

    protected function acquireLock(string $key, int $timeout = 10): bool
    {
        $lock = \Illuminate\Support\Facades\Cache::lock("job:{$key}", $timeout);

        if (!$lock->get()) {
            Log::warning('Failed to acquire lock for job', [
                'job' => get_class($this),
                'lock_key' => $key,
            ]);
            return false;
        }

        return true;
    }
}
