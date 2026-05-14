<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\PayoutAccountResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class PayoutAccountResourceTest extends TestCase
{
    private RecordingClient $http;
    private PayoutAccountResource $resource;

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
        $this->resource = PayoutAccountResource::initiate(transport: $transport);
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"pa_1","status":"verified"}');

        $result = $this->resource->get('pa_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/payout-accounts/pa_1', (string) $req->getUri());
        self::assertSame(['id' => 'pa_1', 'status' => 'verified'], $result);
    }
}
