<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\CourseLessonResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class CourseLessonResourceTest extends TestCase
{
    private RecordingClient $http;
    private CourseLessonResource $resource;

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
        $this->resource = CourseLessonResource::initiate(transport: $transport);
    }

    public function testCreatePostsToCourseLesson(): void
    {
        $this->http->willReturn(200, '{"id":"lsn_1"}');

        $result = $this->resource->create(['title' => 'Lesson One']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/course-lessons', (string) $req->getUri());
        self::assertSame('{"title":"Lesson One"}', (string) $req->getBody());
        self::assertSame(['id' => 'lsn_1'], $result);
    }

    public function testListPassesQuery(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->list(['chapter_id' => 'ch_1']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/course-lessons?chapter_id=ch_1', (string) $req->getUri());
    }

    public function testGetFetchesById(): void
    {
        $this->http->willReturn(200, '{"id":"lsn_1"}');

        $this->resource->get('lsn_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('GET', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/course-lessons/lsn_1', (string) $req->getUri());
    }

    public function testUpdatePatchesById(): void
    {
        $this->http->willReturn(200, '{"id":"lsn_1"}');

        $this->resource->update('lsn_1', ['title' => 'Updated']);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('PATCH', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/course-lessons/lsn_1', (string) $req->getUri());
        self::assertSame('{"title":"Updated"}', (string) $req->getBody());
    }

    public function testStartPostsToStart(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->start('lsn_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/course-lessons/lsn_1/start', (string) $req->getUri());
    }

    public function testMarkAsCompletedPostsToEndpoint(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->markAsCompleted('lsn_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/course-lessons/lsn_1/mark-as-completed', (string) $req->getUri());
    }

    public function testSubmitAssessmentPostsBody(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->submitAssessment('lsn_1', ['answers' => [1, 2]]);

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/course-lessons/lsn_1/submit-assessment', (string) $req->getUri());
        self::assertSame('{"answers":[1,2]}', (string) $req->getBody());
    }

    public function testDeleteRemovesById(): void
    {
        $this->http->willReturn(200, '{}');

        $this->resource->delete('lsn_1');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('DELETE', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/course-lessons/lsn_1', (string) $req->getUri());
    }
}
