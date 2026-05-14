<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Dto\Checkout\CheckoutConfigurationResponse;
use Matchable\Whop\Exception\MissingArgumentsException;
use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\CheckoutResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class CheckoutResourceTest extends TestCase
{
    private RecordingClient $http;
    private CheckoutResource $resource;

    protected function setUp(): void
    {
        $this->http = new RecordingClient();
        $factory = new Psr17Factory();
        $transport = new HttpTransport(
            httpClient: $this->http,
            apiKey: 'apik_test',
            baseUrl: 'https://api.whop.com/api/v1',
            requestFactory: $factory,
            streamFactory: $factory,
        );
        $this->resource = CheckoutResource::initiate(transport: $transport);
    }

    public function testCreatePostsToCheckoutConfigurationsAndReturnsDto(): void
    {
        $this->http->willReturn(200, '{"id":"cc_1","purchase_url":"https://whop.com/checkout/cc_1"}');

        $result = $this->resource->create(['plan_id' => 'plan_1']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/checkout_configurations', (string) $req->getUri());
        self::assertSame('{"plan_id":"plan_1"}', (string) $req->getBody());
        self::assertInstanceOf(CheckoutConfigurationResponse::class, $result);
        self::assertSame('cc_1', $result->id);
    }

    public function testGetFetchesByIdAndReturnsDto(): void
    {
        $this->http->willReturn(200, '{"id":"cc_1","purchase_url":"https://whop.com/checkout/cc_1"}');

        $result = $this->resource->get('cc_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/checkout_configurations/cc_1', (string) $req->getUri());
        self::assertInstanceOf(CheckoutConfigurationResponse::class, $result);
        self::assertSame('cc_1', $result->id);
    }

    public function testCreateWithMissingIdFieldThrowsMissingArgumentsException(): void
    {
        // Response is missing required "id" — DTO factory throws MissingArgumentsException directly.
        $this->http->willReturn(200, '{"purchase_url":"https://whop.com/checkout/cc_1"}');

        $this->expectException(MissingArgumentsException::class);
        $this->resource->create(['plan_id' => 'plan_1']);
    }

    public function testGetWithMissingIdFieldThrowsMissingArgumentsException(): void
    {
        // Response is missing required "id" — DTO factory throws MissingArgumentsException directly.
        $this->http->willReturn(200, '{"purchase_url":"https://whop.com/checkout/cc_1"}');

        $this->expectException(MissingArgumentsException::class);
        $this->resource->get('cc_1');
    }
}
