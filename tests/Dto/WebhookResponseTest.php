<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Dto;

use Matchable\Whop\Dto\Webhook\WebhookResponse;
use Matchable\Whop\Exception\MissingArgumentsException;
use PHPUnit\Framework\TestCase;

final class WebhookResponseTest extends TestCase
{
    public function testFromResponseMapsAllFields(): void
    {
        $webhook = WebhookResponse::fromResponse([
            'id' => 'wh_1',
            'url' => 'https://my.app/webhook',
            'events' => ['payment.paid', 'payment.refunded'],
        ]);

        self::assertSame('wh_1', $webhook->id);
        self::assertSame('https://my.app/webhook', $webhook->url);
        self::assertSame(['payment.paid', 'payment.refunded'], $webhook->events);
    }

    public function testFromResponseDefaultsUrlAndEvents(): void
    {
        $webhook = WebhookResponse::fromResponse(['id' => 'wh_2']);

        self::assertSame('wh_2', $webhook->id);
        self::assertNull($webhook->url);
        self::assertSame([], $webhook->events);
    }

    public function testFromResponseThrowsWhenIdMissing(): void
    {
        $this->expectException(MissingArgumentsException::class);
        WebhookResponse::fromResponse(['url' => 'https://my.app/webhook']);
    }

    public function testFromResponseThrowsWhenIdEmpty(): void
    {
        $this->expectException(MissingArgumentsException::class);
        WebhookResponse::fromResponse(['id' => '']);
    }
}
