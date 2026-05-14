<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Dto;

use Matchable\Whop\Dto\Payment\Payment;
use Matchable\Whop\Exception\MissingArgumentsException;
use PHPUnit\Framework\TestCase;

final class PaymentTest extends TestCase
{
    public function testCreateMapsAllFields(): void
    {
        $payment = Payment::create([
            'id' => 'pay_1',
            'status' => 'paid',
            'sub_status' => 'captured',
            'amount' => 2500,
            'currency' => 'usd',
            'application_fee' => 100,
            'metadata' => ['ref' => 'order_42'],
            'created_at' => '2024-01-01T00:00:00Z',
        ]);

        self::assertSame('pay_1', $payment->id);
        self::assertSame('paid', $payment->status);
        self::assertSame('captured', $payment->subStatus);
        self::assertSame(2500, $payment->amount);
        self::assertSame('USD', $payment->currency);
        self::assertSame(100, $payment->applicationFee);
        self::assertSame(['ref' => 'order_42'], $payment->metadata);
        self::assertSame('2024-01-01T00:00:00Z', $payment->createdAt);
    }

    public function testCreateFallsBackToApplicationFeeAmountKey(): void
    {
        $payment = Payment::create([
            'id' => 'pay_2',
            'status' => 'paid',
            'amount' => 1000,
            'currency' => 'USD',
            'application_fee_amount' => 50,
        ]);

        self::assertSame(50, $payment->applicationFee);
    }

    public function testCreateNormalizesOptionalFieldDefaults(): void
    {
        $payment = Payment::create([
            'id' => 'pay_3',
            'status' => 'pending',
            'amount' => 0,
            'currency' => 'EUR',
        ]);

        self::assertNull($payment->subStatus);
        self::assertSame(0, $payment->amount);
        self::assertSame('EUR', $payment->currency);
        self::assertNull($payment->applicationFee);
        self::assertSame([], $payment->metadata);
        self::assertNull($payment->createdAt);
    }

    public function testCreateUppercasesCurrency(): void
    {
        $payment = Payment::create([
            'id' => 'pay_4',
            'status' => 'paid',
            'amount' => 500,
            'currency' => 'gbp',
        ]);

        self::assertSame('GBP', $payment->currency);
    }

    public function testCreateApplicationFeeKeyTakesPrecedence(): void
    {
        $payment = Payment::create([
            'id' => 'pay_5',
            'status' => 'paid',
            'amount' => 2000,
            'currency' => 'USD',
            'application_fee' => 75,
            'application_fee_amount' => 25,
        ]);

        self::assertSame(75, $payment->applicationFee);
    }

    public function testCreateThrowsWhenIdMissing(): void
    {
        $this->expectException(MissingArgumentsException::class);
        Payment::create(['status' => 'paid', 'amount' => 100, 'currency' => 'USD']);
    }

    public function testCreateThrowsWhenIdEmpty(): void
    {
        $this->expectException(MissingArgumentsException::class);
        Payment::create(['id' => '', 'status' => 'paid', 'amount' => 100, 'currency' => 'USD']);
    }

    public function testCreateThrowsWhenStatusMissing(): void
    {
        $this->expectException(MissingArgumentsException::class);
        Payment::create(['id' => 'pay_6', 'amount' => 100, 'currency' => 'USD']);
    }

    public function testCreateThrowsWhenStatusEmpty(): void
    {
        $this->expectException(MissingArgumentsException::class);
        Payment::create(['id' => 'pay_6', 'status' => '', 'amount' => 100, 'currency' => 'USD']);
    }

    public function testCreateThrowsWhenAmountMissing(): void
    {
        $this->expectException(MissingArgumentsException::class);
        Payment::create(['id' => 'pay_7', 'status' => 'paid', 'currency' => 'USD']);
    }

    public function testCreateThrowsWhenAmountWrongType(): void
    {
        $this->expectException(MissingArgumentsException::class);
        Payment::create(['id' => 'pay_7', 'status' => 'paid', 'amount' => '100', 'currency' => 'USD']);
    }

    public function testCreateThrowsWhenCurrencyMissing(): void
    {
        $this->expectException(MissingArgumentsException::class);
        Payment::create(['id' => 'pay_8', 'status' => 'paid', 'amount' => 100]);
    }

    public function testCreateThrowsWhenCurrencyEmpty(): void
    {
        $this->expectException(MissingArgumentsException::class);
        Payment::create(['id' => 'pay_8', 'status' => 'paid', 'amount' => 100, 'currency' => '']);
    }
}
