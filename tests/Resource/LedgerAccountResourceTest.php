<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\LedgerAccountResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class LedgerAccountResourceTest extends TestCase
{
    private RecordingClient $http;
    private LedgerAccountResource $resource;

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
        $this->resource = LedgerAccountResource::initiate(transport: $transport);
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"la_1","balance":500}');

        $result = $this->resource->get('la_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/ledger-accounts/la_1', (string) $req->getUri());
        self::assertSame(['id' => 'la_1', 'balance' => 500], $result);
    }
}
