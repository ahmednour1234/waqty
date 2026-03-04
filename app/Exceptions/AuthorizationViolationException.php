<?php

namespace App\Exceptions;

use Exception;

class AuthorizationViolationException extends Exception
{
    public function __construct(string $message = 'Authorization check failed')
    {
        parent::__construct($message, 403);
    }
}
