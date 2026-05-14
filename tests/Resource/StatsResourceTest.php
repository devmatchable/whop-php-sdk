<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\StatsResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class StatsResourceTest extends TestCase
{
    private RecordingClient $http;
    private StatsResource $resource;

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
        $this->resource = StatsResource::initiate(transport: $transport);
    }

    public function testDescribePassesQuery(): void
    {
        $this->http->willReturn(200, '{"fields":[]}');

        $result = $this->resource->describe(['entity' => 'payments']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/stats/describe?entity=payments', (string) $req->getUri());
        self::assertSame(['fields' => []], $result);
    }

    public function testMetricPassesQuery(): void
    {
        $this->http->willReturn(200, '{"value":42}');

        $result = $this->resource->metric(['name' => 'revenue']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/stats/metric?name=revenue', (string) $req->getUri());
        self::assertSame(['value' => 42], $result);
    }

    public function testRawPassesQuery(): void
    {
        $this->http->willReturn(200, '{"rows":[]}');

        $result = $this->resource->raw(['table' => 'payments']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/stats/raw?table=payments', (string) $req->getUri());
        self::assertSame(['rows' => []], $result);
    }

    public function testSqlPostsBody(): void
    {
        $this->http->willReturn(200, '{"rows":[]}');

        $result = $this->resource->sql(['query' => 'SELECT 1']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/stats/sql', (string) $req->getUri());
        self::assertSame('{"query":"SELECT 1"}', (string) $req->getBody());
        self::assertSame(['rows' => []], $result);
    }
}
