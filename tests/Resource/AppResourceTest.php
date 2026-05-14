<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\AppResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class AppResourceTest extends TestCase
{
    private RecordingClient $http;
    private AppResource $resource;

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
        $this->resource = AppResource::initiate(transport: $transport);
    }

    public function testCreatePostsToApps(): void
    {
        $this->http->willReturn(200, '{"id":"app_1"}');

        $result = $this->resource->create(['name' => 'My App']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/apps', (string) $req->getUri());
        self::assertSame('{"name":"My App"}', (string) $req->getBody());
        self::assertSame(['id' => 'app_1'], $result);
    }

    public function testListPassesQuery(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->list(['page' => 1]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/apps?page=1', (string) $req->getUri());
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"app_1"}');

        $this->resource->get('app_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/apps/app_1', (string) $req->getUri());
    }

    public function testUpdatePatchesById(): void
    {
        $this->http->willReturn(200, '{"id":"app_1"}');

        $this->resource->update('app_1', ['name' => 'Updated App']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('PATCH', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/apps/app_1', (string) $req->getUri());
        self::assertSame('{"name":"Updated App"}', (string) $req->getBody());
    }
}
