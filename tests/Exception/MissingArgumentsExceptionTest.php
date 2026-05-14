<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Exception;

use Matchable\Whop\Exception\MissingArgumentsException;
use Matchable\Whop\Exception\WhopException;
use PHPUnit\Framework\TestCase;

final class MissingArgumentsExceptionTest extends TestCase
{
    public function testExtendsWhopException(): void
    {
        $exception = new MissingArgumentsException(message: 'field missing');

        self::assertInstanceOf(WhopException::class, $exception);
    }

    public function testMessagePropagates(): void
    {
        $exception = new MissingArgumentsException(message: 'required field absent');

        self::assertSame('required field absent', $exception->getMessage());
    }

    public function testCodeIsZero(): void
    {
        $exception = new MissingArgumentsException(message: 'field missing');

        self::assertSame(0, $exception->getCode());
    }

    public function testPreviousThrowablePropagates(): void
    {
        $previous = new \RuntimeException('root cause');
        $exception = new MissingArgumentsException(message: 'missing field', previous: $previous);

        self::assertSame($previous, $exception->getPrevious());
    }

    public function testPreviousDefaultsToNull(): void
    {
        $exception = new MissingArgumentsException(message: 'no cause');

        self::assertNull($exception->getPrevious());
    }

    public function testForFieldProducesExpectedMessage(): void
    {
        $exception = MissingArgumentsException::forField(context: 'Payment response', field: 'amount');

        self::assertSame('Payment response is missing required field "amount".', $exception->getMessage());
        self::assertInstanceOf(WhopException::class, $exception);
    }

    public function testForFieldReturnsInstance(): void
    {
        $exception = MissingArgumentsException::forField(context: 'Company response', field: 'id');

        self::assertInstanceOf(MissingArgumentsException::class, $exception);
    }
}
