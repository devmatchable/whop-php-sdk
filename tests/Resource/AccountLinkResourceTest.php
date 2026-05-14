<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Dto\AccountLink\AccountLink;
use Matchable\Whop\Exception\MissingArgumentsException;
use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\AccountLinkResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class AccountLinkResourceTest extends TestCase
{
    private RecordingClient $http;
    private AccountLinkResource $resource;

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
        $this->resource = AccountLinkResource::initiate(transport: $transport);
    }

    public function testCreatePostsToAccountLinksAndReturnsDto(): void
    {
        $this->http->willReturn(200, '{"url":"https://whop.com/link/1","expires_at":"2099-01-01T00:00:00Z"}');

        $link = $this->resource->create(['company_id' => 'biz_1']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/account_links', (string) $req->getUri());
        self::assertSame('{"company_id":"biz_1"}', (string) $req->getBody());
        self::assertInstanceOf(AccountLink::class, $link);
        self::assertSame('https://whop.com/link/1', $link->url);
    }

    public function testCreateWithMissingUrlFieldThrowsMissingArgumentsException(): void
    {
        // Response is missing required "url" — DTO factory throws MissingArgumentsException directly.
        $this->http->willReturn(200, '{"expires_at":"2099-01-01T00:00:00Z"}');

        $this->expectException(MissingArgumentsException::class);
        $this->resource->create(['company_id' => 'biz_1']);
    }
}
