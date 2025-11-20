<?php

declare(strict_types=1);

namespace SpunetGestao\JwtAuthConsumer\Exceptions;

use RuntimeException;
use Throwable;

class AccessDeniedException extends RuntimeException
{
    public function __construct(string $message = 'Forbidden', int $code = 403, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return 403;
    }
}
