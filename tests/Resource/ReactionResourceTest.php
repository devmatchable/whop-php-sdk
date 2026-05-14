<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\ReactionResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class ReactionResourceTest extends TestCase
{
    private RecordingClient $http;
    private ReactionResource $resource;

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
        $this->resource = ReactionResource::initiate(transport: $transport);
    }

    public function testCreatePostsToReactions(): void
    {
        $this->http->willReturn(200, '{"id":"rxn_1"}');

        $result = $this->resource->create(['emoji' => '👍']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/reactions', (string) $req->getUri());
        self::assertSame(['id' => 'rxn_1'], $result);
    }

    public function testListPassesQuery(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->list(['message_id' => 'msg_1']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/reactions?message_id=msg_1', (string) $req->getUri());
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"rxn_1"}');

        $this->resource->get('rxn_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/reactions/rxn_1', (string) $req->getUri());
    }

    public function testDeleteRemovesById(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->delete('rxn_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('DELETE', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/reactions/rxn_1', (string) $req->getUri());
    }
}
