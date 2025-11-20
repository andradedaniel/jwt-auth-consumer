<?php

declare(strict_types=1);

namespace SpunetGestao\JwtAuthConsumer\Exceptions;

use RuntimeException;
use Throwable;

class TokenExpiredException extends RuntimeException
{
    public function __construct(string $message = 'Token expired', int $code = 401, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return 401;
    }
}
