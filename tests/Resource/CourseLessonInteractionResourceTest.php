<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\CourseLessonInteractionResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class CourseLessonInteractionResourceTest extends TestCase
{
    private RecordingClient $http;
    private CourseLessonInteractionResource $resource;

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
        $this->resource = CourseLessonInteractionResource::initiate(transport: $transport);
    }

    public function testListPassesQuery(): void
    {
        $this->http->willReturn(200, '{"data":[]}');

        $result = $this->resource->list(['lesson_id' => 'lsn_1']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/course-lesson-interactions?lesson_id=lsn_1', (string) $req->getUri());
        self::assertSame(['data' => []], $result);
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"cli_1"}');

        $this->resource->get('cli_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/course-lesson-interactions/cli_1', (string) $req->getUri());
    }
}
