<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\ForumPostResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class ForumPostResourceTest extends TestCase
{
    private RecordingClient $http;
    private ForumPostResource $resource;

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
        $this->resource = ForumPostResource::initiate(transport: $transport);
    }

    public function testCreatePostsToForumPosts(): void
    {
        $this->http->willReturn(200, '{"id":"fp_1"}');

        $result = $this->resource->create(['content' => 'Hello world']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/forum-posts', (string) $req->getUri());
        self::assertSame('{"content":"Hello world"}', (string) $req->getBody());
        self::assertSame(['id' => 'fp_1'], $result);
    }

    public function testListPassesQuery(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->list(['forum_id' => 'frm_1']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/forum-posts?forum_id=frm_1', (string) $req->getUri());
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"fp_1"}');

        $this->resource->get('fp_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/forum-posts/fp_1', (string) $req->getUri());
    }

    public function testUpdatePatchesById(): void
    {
        $this->http->willReturn(200, '{"id":"fp_1"}');

        $this->resource->update('fp_1', ['content' => 'Updated']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('PATCH', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/forum-posts/fp_1', (string) $req->getUri());
        self::assertSame('{"content":"Updated"}', (string) $req->getBody());
    }
}
