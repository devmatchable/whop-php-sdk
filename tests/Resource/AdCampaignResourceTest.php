<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\AdCampaignResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class AdCampaignResourceTest extends TestCase
{
    private RecordingClient $http;
    private AdCampaignResource $resource;

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
        $this->resource = AdCampaignResource::initiate(transport: $transport);
    }

    public function testCreatePostsToAdCampaigns(): void
    {
        $this->http->willReturn(200, '{"id":"camp_1"}');

        $result = $this->resource->create(['name' => 'Summer']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/ad-campaigns', (string) $req->getUri());
        self::assertSame('{"name":"Summer"}', (string) $req->getBody());
        self::assertSame(['id' => 'camp_1'], $result);
    }

    public function testListPassesQuery(): void
    {
        $this->http->willReturn(200, '{"data":[]}');

        $this->resource->list(['page' => 1]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/ad-campaigns?page=1', (string) $req->getUri());
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"camp_1"}');

        $this->resource->get('camp_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/ad-campaigns/camp_1', (string) $req->getUri());
    }

    public function testUpdatePatchesById(): void
    {
        $this->http->willReturn(200, '{"id":"camp_1"}');

        $this->resource->update('camp_1', ['name' => 'Winter']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('PATCH', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/ad-campaigns/camp_1', (string) $req->getUri());
        self::assertSame('{"name":"Winter"}', (string) $req->getBody());
    }

    public function testPauseWithBodySendsBody(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->pause('camp_1', ['reason' => 'budget']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/ad-campaigns/camp_1/pause', (string) $req->getUri());
        self::assertSame('{"reason":"budget"}', (string) $req->getBody());
    }

    public function testPauseWithNoBodySendsEmptyBody(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->pause('camp_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/ad-campaigns/camp_1/pause', (string) $req->getUri());
        self::assertSame('', (string) $req->getBody());
    }

    public function testUnpausePostsToEndpoint(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->unpause('camp_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/ad-campaigns/camp_1/unpause', (string) $req->getUri());
    }
}
