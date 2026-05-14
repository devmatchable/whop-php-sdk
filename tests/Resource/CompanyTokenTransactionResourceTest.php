<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\CompanyTokenTransactionResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class CompanyTokenTransactionResourceTest extends TestCase
{
    private RecordingClient $http;
    private CompanyTokenTransactionResource $resource;

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
        $this->resource = CompanyTokenTransactionResource::initiate(transport: $transport);
    }

    public function testCreatePostsToCompanyTokenTransactions(): void
    {
        $this->http->willReturn(200, '{"id":"ctt_1"}');

        $result = $this->resource->create(['amount' => 100]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/company-token-transactions', (string) $req->getUri());
        self::assertSame('{"amount":100}', (string) $req->getBody());
        self::assertSame(['id' => 'ctt_1'], $result);
    }

    public function testListPassesQuery(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->list(['page' => 1]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/company-token-transactions?page=1', (string) $req->getUri());
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"ctt_1"}');

        $this->resource->get('ctt_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/company-token-transactions/ctt_1', (string) $req->getUri());
    }
}
