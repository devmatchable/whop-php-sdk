<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\DmMemberResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class DmMemberResourceTest extends TestCase
{
    private RecordingClient $http;
    private DmMemberResource $resource;

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
        $this->resource = DmMemberResource::initiate(transport: $transport);
    }

    public function testCreatePostsToDmMembers(): void
    {
        $this->http->willReturn(200, '{"id":"dmm_1"}');

        $result = $this->resource->create(['channel_id' => 'dm_1']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/dm-members', (string) $req->getUri());
        self::assertSame('{"channel_id":"dm_1"}', (string) $req->getBody());
        self::assertSame(['id' => 'dmm_1'], $result);
    }

    public function testListPassesQuery(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->list(['channel_id' => 'dm_1']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/dm-members?channel_id=dm_1', (string) $req->getUri());
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"dmm_1"}');

        $this->resource->get('dmm_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/dm-members/dmm_1', (string) $req->getUri());
    }

    public function testUpdatePatchesById(): void
    {
        $this->http->willReturn(200, '{"id":"dmm_1"}');

        $this->resource->update('dmm_1', ['role' => 'admin']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('PATCH', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/dm-members/dmm_1', (string) $req->getUri());
        self::assertSame('{"role":"admin"}', (string) $req->getBody());
    }

    public function testDeleteRemovesById(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->delete('dmm_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('DELETE', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/dm-members/dmm_1', (string) $req->getUri());
    }
}
