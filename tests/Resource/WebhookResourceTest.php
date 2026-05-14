<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Dto\Webhook\WebhookResponse;
use Matchable\Whop\Exception\MissingArgumentsException;
use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\WebhookResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class WebhookResourceTest extends TestCase
{
    private RecordingClient $http;
    private WebhookResource $resource;

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
        $this->resource = WebhookResource::initiate(transport: $transport);
    }

    public function testCreatePostsToWebhooksAndReturnsDto(): void
    {
        $this->http->willReturn(200, '{"id":"wh_1","url":"https://example.com/hook","events":["payment.paid"]}');

        $result = $this->resource->create(['url' => 'https://example.com/hook', 'events' => ['payment.paid']]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/webhooks', (string) $req->getUri());
        self::assertInstanceOf(WebhookResponse::class, $result);
        self::assertSame('wh_1', $result->id);
        self::assertSame('https://example.com/hook', $result->url);
    }

    public function testCreateWithMissingIdFieldThrowsMissingArgumentsException(): void
    {
        // Response is missing required "id" — DTO factory throws MissingArgumentsException directly.
        $this->http->willReturn(200, '{"url":"https://example.com/hook"}');

        $this->expectException(MissingArgumentsException::class);
        $this->resource->create(['url' => 'https://example.com/hook']);
    }
}
