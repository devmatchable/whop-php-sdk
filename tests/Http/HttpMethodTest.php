<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Http;

use Matchable\Whop\Http\HttpMethod;
use PHPUnit\Framework\TestCase;

final class HttpMethodTest extends TestCase
{
    public function testConstantsHoldUppercaseHttpVerbs(): void
    {
        self::assertSame('GET', HttpMethod::GET);
        self::assertSame('POST', HttpMethod::POST);
        self::assertSame('PATCH', HttpMethod::PATCH);
        self::assertSame('PUT', HttpMethod::PUT);
        self::assertSame('DELETE', HttpMethod::DELETE);
    }
}
