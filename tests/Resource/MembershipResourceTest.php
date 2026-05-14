<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\MembershipResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class MembershipResourceTest extends TestCase
{
    private RecordingClient $http;
    private MembershipResource $resource;

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
        $this->resource = MembershipResource::initiate(transport: $transport);
    }

    public function testListPassesQuery(): void
    {
        $this->http->willReturn(200, '{"data":[]}');

        $result = $this->resource->list(['page' => 1]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/memberships?page=1', (string) $req->getUri());
        self::assertSame(['data' => []], $result);
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"ms_1"}');

        $this->resource->get('ms_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/memberships/ms_1', (string) $req->getUri());
    }

    public function testUpdatePatchesById(): void
    {
        $this->http->willReturn(200, '{"id":"ms_1"}');

        $this->resource->update('ms_1', ['status' => 'active']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('PATCH', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/memberships/ms_1', (string) $req->getUri());
        self::assertSame('{"status":"active"}', (string) $req->getBody());
    }

    public function testAddFreeDaysPostsBody(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->addFreeDays('ms_1', ['days' => 7]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/memberships/ms_1/add-free-days', (string) $req->getUri());
        self::assertSame('{"days":7}', (string) $req->getBody());
    }

    public function testCancelWithBodySendsBody(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->cancel('ms_1', ['reason' => 'too expensive']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/memberships/ms_1/cancel', (string) $req->getUri());
        self::assertSame('{"reason":"too expensive"}', (string) $req->getBody());
    }

    public function testCancelWithNoBodySendsEmptyBody(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->cancel('ms_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/memberships/ms_1/cancel', (string) $req->getUri());
        self::assertSame('', (string) $req->getBody());
    }

    public function testPauseWithBodySendsBody(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->pause('ms_1', ['until' => '2099-01-01']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/memberships/ms_1/pause', (string) $req->getUri());
        self::assertSame('{"until":"2099-01-01"}', (string) $req->getBody());
    }

    public function testResumeWithBodySendsBody(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->resume('ms_1', ['immediately' => true]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/memberships/ms_1/resume', (string) $req->getUri());
        self::assertSame('{"immediately":true}', (string) $req->getBody());
    }

    public function testPauseWithNoBodySendsEmptyBody(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->pause('ms_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/memberships/ms_1/pause', (string) $req->getUri());
        self::assertSame('', (string) $req->getBody());
    }

    public function testResumeWithNoBodySendsEmptyBody(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->resume('ms_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/memberships/ms_1/resume', (string) $req->getUri());
        self::assertSame('', (string) $req->getBody());
    }

    public function testUncancelPostsToUncancel(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->uncancel('ms_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/memberships/ms_1/uncancel', (string) $req->getUri());
    }
}
