<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Exception;

use Matchable\Whop\Exception\TransportException;
use Matchable\Whop\Exception\WhopException;
use PHPUnit\Framework\TestCase;

final class TransportExceptionTest extends TestCase
{
    public function testExtendsWhopException(): void
    {
        $exception = new TransportException(message: 'connection refused');

        self::assertInstanceOf(WhopException::class, $exception);
    }

    public function testMessagePropagates(): void
    {
        $exception = new TransportException(message: 'timeout');

        self::assertSame('timeout', $exception->getMessage());
    }

    public function testCodeIsZero(): void
    {
        $exception = new TransportException(message: 'timeout');

        self::assertSame(0, $exception->getCode());
    }

    public function testPreviousThrowablePropagates(): void
    {
        $previous = new \RuntimeException('underlying cause');
        $exception = new TransportException(message: 'transport failed', previous: $previous);

        self::assertSame($previous, $exception->getPrevious());
    }

    public function testPreviousDefaultsToNull(): void
    {
        $exception = new TransportException(message: 'no cause');

        self::assertNull($exception->getPrevious());
    }
}
