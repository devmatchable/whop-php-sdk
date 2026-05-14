<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\RefundResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class RefundResourceTest extends TestCase
{
    private RecordingClient $http;
    private RefundResource $resource;

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
        $this->resource = RefundResource::initiate(transport: $transport);
    }

    public function testListPassesQuery(): void
    {
        $this->http->willReturn(200, '{"data":[]}');

        $result = $this->resource->list(['page' => 1]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/refunds?page=1', (string) $req->getUri());
        self::assertSame(['data' => []], $result);
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"ref_1"}');

        $this->resource->get('ref_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/refunds/ref_1', (string) $req->getUri());
    }
}
