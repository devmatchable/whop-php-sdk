<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Exception;

use Matchable\Whop\Exception\WebhookVerificationException;
use Matchable\Whop\Exception\WhopException;
use PHPUnit\Framework\TestCase;

final class WebhookVerificationExceptionTest extends TestCase
{
    public function testExtendsWhopException(): void
    {
        $exception = new WebhookVerificationException(message: 'signature mismatch');

        self::assertInstanceOf(WhopException::class, $exception);
    }

    public function testMessagePropagates(): void
    {
        $exception = new WebhookVerificationException(message: 'missing headers');

        self::assertSame('missing headers', $exception->getMessage());
    }

    public function testCodeIsZero(): void
    {
        $exception = new WebhookVerificationException(message: 'stale timestamp');

        self::assertSame(0, $exception->getCode());
    }

    public function testPreviousThrowablePropagates(): void
    {
        $previous = new \RuntimeException('underlying cause');
        $exception = new WebhookVerificationException(message: 'verification failed', previous: $previous);

        self::assertSame($previous, $exception->getPrevious());
    }

    public function testPreviousDefaultsToNull(): void
    {
        $exception = new WebhookVerificationException(message: 'no cause');

        self::assertNull($exception->getPrevious());
    }
}
