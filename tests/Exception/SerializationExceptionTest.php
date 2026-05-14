<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Exception;

use Matchable\Whop\Exception\SerializationException;
use Matchable\Whop\Exception\WhopException;
use PHPUnit\Framework\TestCase;

final class SerializationExceptionTest extends TestCase
{
    public function testExtendsWhopException(): void
    {
        $exception = new SerializationException(message: 'json decode failed');

        self::assertInstanceOf(WhopException::class, $exception);
    }

    public function testMessagePropagates(): void
    {
        $exception = new SerializationException(message: 'Unexpected token');

        self::assertSame('Unexpected token', $exception->getMessage());
    }

    public function testCodeIsZero(): void
    {
        $exception = new SerializationException(message: 'json error');

        self::assertSame(0, $exception->getCode());
    }

    public function testPreviousThrowablePropagates(): void
    {
        $previous = new \JsonException('Syntax error');
        $exception = new SerializationException(message: 'json error', previous: $previous);

        self::assertSame($previous, $exception->getPrevious());
    }

    public function testPreviousDefaultsToNull(): void
    {
        $exception = new SerializationException(message: 'no cause');

        self::assertNull($exception->getPrevious());
    }
}
