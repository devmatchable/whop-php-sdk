<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\AdGroupResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class AdGroupResourceTest extends TestCase
{
    private RecordingClient $http;
    private AdGroupResource $resource;

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
        $this->resource = AdGroupResource::initiate(transport: $transport);
    }

    public function testCreatePostsToAdGroups(): void
    {
        $this->http->willReturn(200, '{"id":"grp_1"}');

        $result = $this->resource->create(['name' => 'Group A']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/ad-groups', (string) $req->getUri());
        self::assertSame('{"name":"Group A"}', (string) $req->getBody());
        self::assertSame(['id' => 'grp_1'], $result);
    }

    public function testListPassesQuery(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->list(['page' => 2]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/ad-groups?page=2', (string) $req->getUri());
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"grp_1"}');

        $this->resource->get('grp_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/ad-groups/grp_1', (string) $req->getUri());
    }

    public function testUpdatePatchesById(): void
    {
        $this->http->willReturn(200, '{"id":"grp_1"}');

        $this->resource->update('grp_1', ['name' => 'Group B']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('PATCH', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/ad-groups/grp_1', (string) $req->getUri());
        self::assertSame('{"name":"Group B"}', (string) $req->getBody());
    }

    public function testDeleteRemovesById(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->delete('grp_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('DELETE', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/ad-groups/grp_1', (string) $req->getUri());
    }
}
