<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Exception;

use Matchable\Whop\Exception\WhopApiException;
use Matchable\Whop\Exception\WhopException;
use PHPUnit\Framework\TestCase;

final class WhopApiExceptionTest extends TestCase
{
    public function testConstructorExposesStatusCodeAndBody(): void
    {
        $exception = new WhopApiException(
            statusCode: 422,
            message: 'boom',
            responseBody: ['message' => 'boom'],
        );

        self::assertSame(422, $exception->statusCode);
        self::assertSame(['message' => 'boom'], $exception->responseBody);
        self::assertSame(422, $exception->getCode());
        self::assertSame('boom', $exception->getMessage());
    }

    public function testWhopApiExceptionExtendsWhopException(): void
    {
        $exception = new WhopApiException(statusCode: 500, message: 'server error');

        self::assertInstanceOf(WhopException::class, $exception);
    }

    public function testFromResponseUsesTopLevelMessage(): void
    {
        $exception = WhopApiException::fromResponse(statusCode: 404, responseBody: ['message' => 'not found']);

        self::assertSame(404, $exception->statusCode);
        self::assertStringContainsString('not found', $exception->getMessage());
        self::assertStringContainsString('404', $exception->getMessage());
    }

    public function testFromResponseFallsBackToNestedErrorMessage(): void
    {
        $exception = WhopApiException::fromResponse(statusCode: 500, responseBody: ['error' => ['message' => 'server error']]);

        self::assertStringContainsString('server error', $exception->getMessage());
    }

    public function testFromResponseFallsBackToUnknownError(): void
    {
        $exception = WhopApiException::fromResponse(statusCode: 503, responseBody: []);

        self::assertStringContainsString('Unknown Whop API error', $exception->getMessage());
    }
}
