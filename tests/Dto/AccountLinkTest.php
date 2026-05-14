<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Dto;

use Matchable\Whop\Dto\AccountLink\AccountLink;
use Matchable\Whop\Exception\MissingArgumentsException;
use PHPUnit\Framework\TestCase;

final class AccountLinkTest extends TestCase
{
    public function testFromResponseMapsAllFields(): void
    {
        $link = AccountLink::fromResponse([
            'url' => 'https://whop.com/oauth/connect',
            'expires_at' => '2024-12-31T23:59:59Z',
        ]);

        self::assertSame('https://whop.com/oauth/connect', $link->url);
        self::assertSame('2024-12-31T23:59:59Z', $link->expiresAt);
    }

    public function testFromResponseThrowsWhenUrlMissing(): void
    {
        $this->expectException(MissingArgumentsException::class);
        AccountLink::fromResponse(['expires_at' => '2024-12-31T23:59:59Z']);
    }

    public function testFromResponseThrowsWhenUrlEmpty(): void
    {
        $this->expectException(MissingArgumentsException::class);
        AccountLink::fromResponse(['url' => '', 'expires_at' => '2024-12-31T23:59:59Z']);
    }

    public function testFromResponseThrowsWhenExpiresAtMissing(): void
    {
        $this->expectException(MissingArgumentsException::class);
        AccountLink::fromResponse(['url' => 'https://whop.com/oauth/connect']);
    }

    public function testFromResponseThrowsWhenExpiresAtEmpty(): void
    {
        $this->expectException(MissingArgumentsException::class);
        AccountLink::fromResponse(['url' => 'https://whop.com/oauth/connect', 'expires_at' => '']);
    }
}
