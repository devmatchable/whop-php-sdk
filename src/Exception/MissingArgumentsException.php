<?php

declare(strict_types=1);

namespace Matchable\Whop\Exception;

final class MissingArgumentsException extends WhopException
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct(message: $message, code: 0, previous: $previous);
    }

    public static function forField(string $context, string $field): self
    {
        return new self(sprintf('%s is missing required field "%s".', $context, $field));
    }
}
