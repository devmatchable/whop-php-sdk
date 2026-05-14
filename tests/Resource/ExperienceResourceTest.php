<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\ExperienceResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class ExperienceResourceTest extends TestCase
{
    private RecordingClient $http;
    private ExperienceResource $resource;

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
        $this->resource = ExperienceResource::initiate(transport: $transport);
    }

    public function testCreatePostsToExperiences(): void
    {
        $this->http->willReturn(200, '{"id":"exp_1"}');

        $result = $this->resource->create(['name' => 'Exp One']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/experiences', (string) $req->getUri());
        self::assertSame('{"name":"Exp One"}', (string) $req->getBody());
        self::assertSame(['id' => 'exp_1'], $result);
    }

    public function testListPassesQuery(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->list(['page' => 1]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/experiences?page=1', (string) $req->getUri());
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"exp_1"}');

        $this->resource->get('exp_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/experiences/exp_1', (string) $req->getUri());
    }

    public function testUpdatePatchesById(): void
    {
        $this->http->willReturn(200, '{"id":"exp_1"}');

        $this->resource->update('exp_1', ['name' => 'Exp Updated']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('PATCH', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/experiences/exp_1', (string) $req->getUri());
        self::assertSame('{"name":"Exp Updated"}', (string) $req->getBody());
    }

    public function testAttachPostsToAttach(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->attach('exp_1', ['product_id' => 'prd_1']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/experiences/exp_1/attach', (string) $req->getUri());
        self::assertSame('{"product_id":"prd_1"}', (string) $req->getBody());
    }

    public function testDetachPostsToDetach(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->detach('exp_1', ['product_id' => 'prd_1']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/experiences/exp_1/detach', (string) $req->getUri());
        self::assertSame('{"product_id":"prd_1"}', (string) $req->getBody());
    }

    public function testDuplicatePostsToDuplicate(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->duplicate('exp_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/experiences/exp_1/duplicate', (string) $req->getUri());
    }

    public function testDeleteRemovesById(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->delete('exp_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('DELETE', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/experiences/exp_1', (string) $req->getUri());
    }
}
