<?php

declare(strict_types=1);

namespace Matchable\Whop\Exception;

final class SerializationException extends WhopException
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct(message: $message, code: 0, previous: $previous);
    }
}
