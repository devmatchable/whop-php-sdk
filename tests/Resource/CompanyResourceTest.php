<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Dto\Company\Company;
use Matchable\Whop\Exception\MissingArgumentsException;
use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\CompanyResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class CompanyResourceTest extends TestCase
{
    private RecordingClient $http;
    private CompanyResource $resource;

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
        $this->resource = CompanyResource::initiate(transport: $transport);
    }

    public function testCreatePostsToCompaniesAndReturnsDto(): void
    {
        $this->http->willReturn(200, '{"id":"biz_1","title":"Acme"}');

        $company = $this->resource->create(['title' => 'Acme']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/companies', (string) $req->getUri());
        self::assertSame('{"title":"Acme"}', (string) $req->getBody());
        self::assertInstanceOf(Company::class, $company);
        self::assertSame('biz_1', $company->id);
    }

    public function testGetFetchesByIdAndReturnsDto(): void
    {
        $this->http->willReturn(200, '{"id":"biz_1","title":"Acme"}');

        $company = $this->resource->get('biz_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/companies/biz_1', (string) $req->getUri());
        self::assertSame('biz_1', $company->id);
    }

    public function testListPassesQueryAndReturnsArray(): void
    {
        $this->http->willReturn(200, '{"data":[]}');

        $result = $this->resource->list(['page' => 2]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/companies?page=2', (string) $req->getUri());
        self::assertSame(['data' => []], $result);
    }

    public function testGetWithMissingIdFieldThrowsMissingArgumentsException(): void
    {
        // Response is missing the required "id" field — DTO factory throws MissingArgumentsException directly.
        $this->http->willReturn(200, '{"title":"Acme"}');

        $this->expectException(MissingArgumentsException::class);
        $this->resource->get('biz_1');
    }

    public function testCreateWithMissingIdFieldThrowsMissingArgumentsException(): void
    {
        // Response is missing the required "id" field — DTO factory throws MissingArgumentsException directly.
        $this->http->willReturn(200, '{"title":"Acme"}');

        $this->expectException(MissingArgumentsException::class);
        $this->resource->create(['title' => 'Acme']);
    }
}
