<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\NotificationResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class NotificationResourceTest extends TestCase
{
    private RecordingClient $http;
    private NotificationResource $resource;

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
        $this->resource = NotificationResource::initiate(transport: $transport);
    }

    public function testCreatePostsToNotifications(): void
    {
        $this->http->willReturn(200, '{"id":"notif_1"}');

        $result = $this->resource->create(['message' => 'Hello']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/notifications', (string) $req->getUri());
        self::assertSame('{"message":"Hello"}', (string) $req->getBody());
        self::assertSame(['id' => 'notif_1'], $result);
    }
}
