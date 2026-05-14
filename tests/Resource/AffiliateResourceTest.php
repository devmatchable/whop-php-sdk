<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\AffiliateResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class AffiliateResourceTest extends TestCase
{
    private RecordingClient $http;
    private AffiliateResource $resource;

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
        $this->resource = AffiliateResource::initiate(transport: $transport);
    }

    public function testCreatePostsToAffiliates(): void
    {
        $this->http->willReturn(200, '{"id":"aff_1"}');

        $result = $this->resource->create(['user_id' => 'usr_1']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/affiliates', (string) $req->getUri());
        self::assertSame('{"user_id":"usr_1"}', (string) $req->getBody());
        self::assertSame(['id' => 'aff_1'], $result);
    }

    public function testListPassesQuery(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->list(['page' => 1]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/affiliates?page=1', (string) $req->getUri());
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"aff_1"}');

        $this->resource->get('aff_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/affiliates/aff_1', (string) $req->getUri());
    }

    public function testArchivePostsToArchive(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->archive('aff_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/affiliates/aff_1/archive', (string) $req->getUri());
    }

    public function testUnarchivePostsToUnarchive(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->unarchive('aff_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/affiliates/aff_1/unarchive', (string) $req->getUri());
    }

    public function testCreateOverridePostsToOverrides(): void
    {
        $this->http->willReturn(200, '{"id":"ovr_1"}');

        $this->resource->createOverride('aff_1', ['rate' => 10]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/affiliates/aff_1/overrides', (string) $req->getUri());
        self::assertSame('{"rate":10}', (string) $req->getBody());
    }

    public function testListOverridesPassesQuery(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->listOverrides('aff_1', ['page' => 1]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/affiliates/aff_1/overrides?page=1', (string) $req->getUri());
    }

    public function testGetOverrideFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"ovr_1"}');

        $this->resource->getOverride('aff_1', 'ovr_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/affiliates/aff_1/overrides/ovr_1', (string) $req->getUri());
    }

    public function testUpdateOverridePatchesById(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->updateOverride('aff_1', 'ovr_1', ['rate' => 20]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('PATCH', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/affiliates/aff_1/overrides/ovr_1', (string) $req->getUri());
        self::assertSame('{"rate":20}', (string) $req->getBody());
    }

    public function testDeleteOverrideRemovesById(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->deleteOverride('aff_1', 'ovr_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('DELETE', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/affiliates/aff_1/overrides/ovr_1', (string) $req->getUri());
    }
}
