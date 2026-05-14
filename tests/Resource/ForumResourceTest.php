<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\ForumResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class ForumResourceTest extends TestCase
{
    private RecordingClient $http;
    private ForumResource $resource;

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
        $this->resource = ForumResource::initiate(transport: $transport);
    }

    public function testListPassesQuery(): void
    {
        $this->http->willReturn(200, '{"data":[]}');

        $result = $this->resource->list(['page' => 1]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/forums?page=1', (string) $req->getUri());
        self::assertSame(['data' => []], $result);
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"frm_1"}');

        $this->resource->get('frm_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/forums/frm_1', (string) $req->getUri());
    }

    public function testUpdatePatchesById(): void
    {
        $this->http->willReturn(200, '{"id":"frm_1"}');

        $this->resource->update('frm_1', ['name' => 'General']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('PATCH', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/forums/frm_1', (string) $req->getUri());
        self::assertSame('{"name":"General"}', (string) $req->getBody());
    }
}
