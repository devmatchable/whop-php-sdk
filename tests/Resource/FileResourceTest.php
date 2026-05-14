<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Resource;

use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Resource\FileResource;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class FileResourceTest extends TestCase
{
    private RecordingClient $http;
    private FileResource $resource;

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
        $this->resource = FileResource::initiate(transport: $transport);
    }

    public function testCreatePostsFilenameToFiles(): void
    {
        $this->http->willReturn(200, '{"id":"file_1","upload_url":"https://s3.example.com/upload"}');

        $result = $this->resource->create('photo.jpg');

        self::assertNotNull($this->http->lastRequest);
        $req = $this->http->lastRequest;
        self::assertSame('POST', $req->getMethod());
        self::assertSame('https://api.whop.com/api/v1/files', (string) $req->getUri());
        self::assertSame('{"filename":"photo.jpg"}', (string) $req->getBody());
        self::assertSame(['id' => 'file_1', 'upload_url' => 'https://s3.example.com/upload'], $result);
    }
}
