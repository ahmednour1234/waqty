<?php

namespace App\Exceptions;

use Exception;

class PerformanceViolationException extends Exception
{
    public function __construct(string $message = 'Performance threshold exceeded')
    {
        parent::__construct($message, 500);
    }
}
