<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\AppBuildResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class AppBuildResourceTest extends TestCase
{
    private RecordingClient $http;
    private AppBuildResource $resource;

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
        $this->resource = AppBuildResource::initiate(transport: $transport);
    }

    public function testCreatePostsToAppBuilds(): void
    {
        $this->http->willReturn(200, '{"id":"build_1"}');

        $result = $this->resource->create(['app_id' => 'app_1']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/app-builds', (string) $req->getUri());
        self::assertSame('{"app_id":"app_1"}', (string) $req->getBody());
        self::assertSame(['id' => 'build_1'], $result);
    }

    public function testListPassesQuery(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->list(['app_id' => 'app_1']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/app-builds?app_id=app_1', (string) $req->getUri());
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"build_1"}');

        $this->resource->get('build_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/app-builds/build_1', (string) $req->getUri());
    }

    public function testPromotePostsToPromote(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->promote('build_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/app-builds/build_1/promote', (string) $req->getUri());
    }
}
