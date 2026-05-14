<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests;

use Matchable\Whop\Resource\CheckoutResource;
use Matchable\Whop\Resource\CompanyResource;
use Matchable\Whop\Resource\PaymentResource;
use Matchable\Whop\Resource\WebhookResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Matchable\Whop\WhopApiClient;
use PHPUnit\Framework\TestCase;

final class WhopApiClientTest extends TestCase
{
    public function testWiresResourcePropertiesWhenFactoriesAreDiscovered(): void
    {
        // factories null -> resolved via php-http/discovery (nyholm/psr7 is installed)
        $client = new WhopApiClient(httpClient: new RecordingClient(), apiKey: 'apik_test');

        self::assertInstanceOf(CompanyResource::class, $client->companies);
        self::assertInstanceOf(CheckoutResource::class, $client->checkouts);
        self::assertInstanceOf(PaymentResource::class, $client->payments);
        self::assertInstanceOf(WebhookResource::class, $client->webhooks);
    }

    public function testResourceCallGoesThroughTheInjectedClient(): void
    {
        $http = new RecordingClient();
        $http->willReturn(200, '{"id":"biz_1","title":"Acme"}');

        $client = new WhopApiClient(httpClient: $http, apiKey: 'apik_test', baseUrl: 'https://api.whop.com/api/v1');
        $company = $client->companies->get('biz_1');

        self::assertNotNull($http->lastRequest);
        self::assertSame('https://api.whop.com/api/v1/companies/biz_1', (string) $http->lastRequest->getUri());
        self::assertSame('biz_1', $company->id);
    }

    public function testEveryResourcePropertyIsInitialised(): void
    {
        $client = new WhopApiClient(httpClient: new RecordingClient(), apiKey: 'apik_test');

        $reflection = new \ReflectionClass($client);
        foreach ($reflection->getProperties() as $property) {
            self::assertTrue($property->isInitialized($client), $property->getName().' is not initialised');
        }
    }
}
