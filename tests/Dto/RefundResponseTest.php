<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Dto;

use Matchable\Whop\Dto\Payment\RefundResponse;
use Matchable\Whop\Exception\MissingArgumentsException;
use PHPUnit\Framework\TestCase;

final class RefundResponseTest extends TestCase
{
    public function testFromResponseMapsAllFields(): void
    {
        $refund = RefundResponse::fromResponse([
            'refund_id' => 'ref_1',
            'payment_id' => 'pay_1',
            'status' => 'succeeded',
            'amount' => 1000,
        ]);

        self::assertSame('ref_1', $refund->refundId);
        self::assertSame('pay_1', $refund->paymentId);
        self::assertSame('succeeded', $refund->status);
        self::assertSame(1000, $refund->amount);
    }

    public function testFromResponseFallsBackToIdKey(): void
    {
        $refund = RefundResponse::fromResponse([
            'id' => 'ref_2',
        ]);

        self::assertSame('ref_2', $refund->refundId);
    }

    public function testRefundIdKeyTakesPrecedenceOverIdKey(): void
    {
        $refund = RefundResponse::fromResponse([
            'refund_id' => 'ref_primary',
            'id' => 'ref_fallback',
        ]);

        self::assertSame('ref_primary', $refund->refundId);
    }

    public function testFromResponseDefaults(): void
    {
        $refund = RefundResponse::fromResponse(['refund_id' => 'ref_3']);

        self::assertNull($refund->paymentId);
        self::assertNull($refund->status);
        self::assertNull($refund->amount);
    }

    public function testFromResponseThrowsWhenBothIdsMissing(): void
    {
        $this->expectException(MissingArgumentsException::class);
        RefundResponse::fromResponse(['payment_id' => 'pay_1', 'status' => 'succeeded']);
    }

    public function testFromResponseThrowsWhenRefundIdEmpty(): void
    {
        $this->expectException(MissingArgumentsException::class);
        RefundResponse::fromResponse(['refund_id' => '']);
    }
}
