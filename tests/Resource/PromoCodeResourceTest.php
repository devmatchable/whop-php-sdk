<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\PromoCodeResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class PromoCodeResourceTest extends TestCase
{
    private RecordingClient $http;
    private PromoCodeResource $resource;

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
        $this->resource = PromoCodeResource::initiate(transport: $transport);
    }

    public function testCreatePostsToPromoCodes(): void
    {
        $this->http->willReturn(200, '{"id":"pc_1"}');

        $result = $this->resource->create(['code' => 'SAVE10']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/promo-codes', (string) $req->getUri());
        self::assertSame('{"code":"SAVE10"}', (string) $req->getBody());
        self::assertSame(['id' => 'pc_1'], $result);
    }

    public function testListPassesQuery(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->list(['page' => 1]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/promo-codes?page=1', (string) $req->getUri());
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"pc_1"}');

        $this->resource->get('pc_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/promo-codes/pc_1', (string) $req->getUri());
    }

    public function testDeleteRemovesById(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->delete('pc_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('DELETE', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/promo-codes/pc_1', (string) $req->getUri());
    }
}
