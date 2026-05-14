<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\TopupResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class TopupResourceTest extends TestCase
{
    private RecordingClient $http;
    private TopupResource $resource;

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
        $this->resource = TopupResource::initiate(transport: $transport);
    }

    public function testCreatePostsToTopups(): void
    {
        $this->http->willReturn(200, '{"id":"top_1"}');

        $result = $this->resource->create(['amount' => 500]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/topups', (string) $req->getUri());
        self::assertSame('{"amount":500}', (string) $req->getBody());
        self::assertSame(['id' => 'top_1'], $result);
    }
}
