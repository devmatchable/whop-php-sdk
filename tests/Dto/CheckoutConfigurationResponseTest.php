<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Dto;

use Matchable\Whop\Dto\Checkout\CheckoutConfigurationResponse;
use Matchable\Whop\Exception\MissingArgumentsException;
use PHPUnit\Framework\TestCase;

final class CheckoutConfigurationResponseTest extends TestCase
{
    public function testFromResponseMapsAllFields(): void
    {
        $response = CheckoutConfigurationResponse::fromResponse([
            'id' => 'ch_1',
            'purchase_url' => 'https://buy.whop.com/ch_1',
            'plan' => ['id' => 'plan_abc'],
            'status' => 'open',
            'currency' => 'EUR',
            'final_price_amount' => 1500,
            'metadata' => ['order_id' => '42'],
        ]);

        self::assertSame('ch_1', $response->id);
        self::assertSame('https://buy.whop.com/ch_1', $response->purchaseUrl);
        self::assertSame('plan_abc', $response->planId);
        self::assertSame('open', $response->status);
        self::assertSame('EUR', $response->currency);
        self::assertSame(1500, $response->finalPriceAmount);
        self::assertSame(['order_id' => '42'], $response->metadata);
    }

    public function testFromResponseFallsBackToUrlKey(): void
    {
        $response = CheckoutConfigurationResponse::fromResponse([
            'id' => 'ch_2',
            'url' => 'https://buy.whop.com/ch_2',
        ]);

        self::assertSame('https://buy.whop.com/ch_2', $response->purchaseUrl);
    }

    public function testFromResponseFallsBackToPlanIdKey(): void
    {
        $response = CheckoutConfigurationResponse::fromResponse([
            'id' => 'ch_3',
            'purchase_url' => 'https://buy.whop.com/ch_3',
            'plan_id' => 'plan_xyz',
        ]);

        self::assertSame('plan_xyz', $response->planId);
    }

    public function testFromResponseDefaults(): void
    {
        $response = CheckoutConfigurationResponse::fromResponse([
            'id' => 'ch_4',
            'purchase_url' => 'https://buy.whop.com/ch_4',
        ]);

        self::assertNull($response->planId);
        self::assertNull($response->status);
        self::assertNull($response->currency);
        self::assertNull($response->finalPriceAmount);
        self::assertSame([], $response->metadata);
    }

    public function testFromResponseThrowsWhenIdMissing(): void
    {
        $this->expectException(MissingArgumentsException::class);
        CheckoutConfigurationResponse::fromResponse(['purchase_url' => 'https://x']);
    }

    public function testFromResponseThrowsWhenIdEmpty(): void
    {
        $this->expectException(MissingArgumentsException::class);
        CheckoutConfigurationResponse::fromResponse(['id' => '', 'purchase_url' => 'https://x']);
    }

    public function testFromResponseThrowsWhenPurchaseUrlMissing(): void
    {
        $this->expectException(MissingArgumentsException::class);
        CheckoutConfigurationResponse::fromResponse(['id' => 'ch_5']);
    }

    public function testFromResponseThrowsWhenPurchaseUrlEmpty(): void
    {
        $this->expectException(MissingArgumentsException::class);
        CheckoutConfigurationResponse::fromResponse(['id' => 'ch_5', 'purchase_url' => '']);
    }

    public function testPlanKeyTakesPrecedenceOverPlanIdKey(): void
    {
        $response = CheckoutConfigurationResponse::fromResponse([
            'id' => 'ch_6',
            'purchase_url' => 'https://buy.whop.com/ch_6',
            'plan' => ['id' => 'plan_nested'],
            'plan_id' => 'plan_flat',
        ]);

        self::assertSame('plan_nested', $response->planId);
    }

    public function testPurchaseUrlTakesPrecedenceOverUrlWhenBothPresent(): void
    {
        $response = CheckoutConfigurationResponse::fromResponse([
            'id' => 'ch_7',
            'purchase_url' => 'https://buy.whop.com/purchase',
            'url' => 'https://buy.whop.com/url',
        ]);

        self::assertSame('https://buy.whop.com/purchase', $response->purchaseUrl);
    }

    public function testMetadataWithMultipleStringKeysPreservesAllEntries(): void
    {
        $response = CheckoutConfigurationResponse::fromResponse([
            'id' => 'ch_8',
            'purchase_url' => 'https://buy.whop.com/ch_8',
            'metadata' => [
                'order_id' => '99',
                'source' => 'campaign_a',
                'ref' => 'abc123',
            ],
        ]);

        self::assertSame([
            'order_id' => '99',
            'source' => 'campaign_a',
            'ref' => 'abc123',
        ], $response->metadata);
    }
}
