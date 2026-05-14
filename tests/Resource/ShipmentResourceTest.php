<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\ShipmentResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class ShipmentResourceTest extends TestCase
{
    private RecordingClient $http;
    private ShipmentResource $resource;

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
        $this->resource = ShipmentResource::initiate(transport: $transport);
    }

    public function testCreatePostsToShipments(): void
    {
        $this->http->willReturn(200, '{"id":"shp_1"}');

        $result = $this->resource->create(['tracking_number' => 'TRACK123']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/shipments', (string) $req->getUri());
        self::assertSame('{"tracking_number":"TRACK123"}', (string) $req->getBody());
        self::assertSame(['id' => 'shp_1'], $result);
    }

    public function testListPassesQuery(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->list(['page' => 1]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/shipments?page=1', (string) $req->getUri());
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"shp_1"}');

        $this->resource->get('shp_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/shipments/shp_1', (string) $req->getUri());
    }
}
