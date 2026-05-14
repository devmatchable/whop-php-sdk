<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\AccessTokenResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class AccessTokenResourceTest extends TestCase
{
    private RecordingClient $http;
    private AccessTokenResource $resource;

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
        $this->resource = AccessTokenResource::initiate(transport: $transport);
    }

    public function testCreatePostsToAccessTokensAndReturnsArray(): void
    {
        $this->http->willReturn(200, '{"token":"tok_1"}');

        $result = $this->resource->create(['user_id' => 'usr_1']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/access_tokens', (string) $req->getUri());
        self::assertSame('{"user_id":"usr_1"}', (string) $req->getBody());
        self::assertSame(['token' => 'tok_1'], $result);
    }
}
