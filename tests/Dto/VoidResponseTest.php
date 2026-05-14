<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Dto;

use Matchable\Whop\Dto\Payment\VoidResponse;
use Matchable\Whop\Exception\MissingArgumentsException;
use PHPUnit\Framework\TestCase;

final class VoidResponseTest extends TestCase
{
    public function testFromResponseMapsAllFields(): void
    {
        $void = VoidResponse::fromResponse([
            'id' => 'void_1',
            'status' => 'voided',
        ]);

        self::assertSame('void_1', $void->id);
        self::assertSame('voided', $void->status);
    }

    public function testFromResponseDefaultsStatusToNull(): void
    {
        $void = VoidResponse::fromResponse(['id' => 'void_2']);

        self::assertSame('void_2', $void->id);
        self::assertNull($void->status);
    }

    public function testFromResponseThrowsWhenIdMissing(): void
    {
        $this->expectException(MissingArgumentsException::class);
        VoidResponse::fromResponse(['status' => 'voided']);
    }

    public function testFromResponseThrowsWhenIdEmpty(): void
    {
        $this->expectException(MissingArgumentsException::class);
        VoidResponse::fromResponse(['id' => '']);
    }
}
