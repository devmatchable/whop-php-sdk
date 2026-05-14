<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Http;

use Matchable\Whop\Exception\SerializationException;
use Matchable\Whop\Exception\TransportException;
use Matchable\Whop\Exception\WhopApiException;
use Matchable\Whop\Exception\WhopException;
use Matchable\Whop\Http\HttpMethod;
use Matchable\Whop\Http\HttpTransport;
use Matchable\Whop\Tests\Support\RecordingClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;

final class HttpTransportTest extends TestCase
{
    private function transport(RecordingClient $client): HttpTransport
    {
        $factory = new Psr17Factory();

        return new HttpTransport(
            httpClient: $client,
            apiKey: 'apik_test',
            baseUrl: 'https://api.whop.com/api/v1',
            requestFactory: $factory,
            streamFactory: $factory,
        );
    }

    public function testGetRequestBuildsUriAndAuthHeader(): void
    {
        $client = new RecordingClient();
        $client->willReturn(200, '{"id":"biz_1"}');

        $result = $this->transport($client)->request(method: HttpMethod::GET, path: 'companies/biz_1');

        self::assertNotNull($client->lastRequest);
        self::assertSame('GET', $client->lastRequest->getMethod());
        self::assertSame('https://api.whop.com/api/v1/companies/biz_1', (string) $client->lastRequest->getUri());
        self::assertSame('Bearer apik_test', $client->lastRequest->getHeaderLine('Authorization'));
        self::assertSame('application/json', $client->lastRequest->getHeaderLine('Accept'));
        self::assertSame(['id' => 'biz_1'], $result);
    }

    public function testQueryParametersAreAppendedToUri(): void
    {
        $client = new RecordingClient();
        $client->willReturn(200, '{}');

        $this->transport($client)->request(method: HttpMethod::GET, path: 'payments', query: ['page' => 2, 'status' => 'paid']);

        self::assertNotNull($client->lastRequest);
        self::assertSame('https://api.whop.com/api/v1/payments?page=2&status=paid', (string) $client->lastRequest->getUri());
    }

    public function testBodyIsJsonEncodedIntoRequestStream(): void
    {
        $client = new RecordingClient();
        $client->willReturn(200, '{}');

        $this->transport($client)->request(method: HttpMethod::POST, path: 'companies', body: ['title' => 'Acme']);

        self::assertNotNull($client->lastRequest);
        self::assertSame('POST', $client->lastRequest->getMethod());
        self::assertSame('{"title":"Acme"}', (string) $client->lastRequest->getBody());
        self::assertSame('application/json', $client->lastRequest->getHeaderLine('Content-Type'));
    }

    public function testErrorResponseThrowsWhopApiExceptionWithStatusAndBody(): void
    {
        $client = new RecordingClient();
        $client->willReturn(422, '{"message":"invalid"}');

        try {
            $this->transport($client)->request(method: HttpMethod::POST, path: 'companies', body: ['x' => 1]);
            self::fail('Expected WhopApiException');
        } catch (WhopApiException $exception) {
            self::assertSame(422, $exception->statusCode);
            self::assertSame(['message' => 'invalid'], $exception->responseBody);
        }
    }

    public function testTransportExceptionIsWrapped(): void
    {
        $client = new RecordingClient();
        $client->willThrow(new class('network down') extends \RuntimeException implements ClientExceptionInterface {});

        $this->expectException(TransportException::class);

        try {
            $this->transport($client)->request(method: HttpMethod::GET, path: 'companies');
        } catch (TransportException $exception) {
            self::assertStringContainsString('network down', $exception->getMessage());
            self::assertInstanceOf(WhopException::class, $exception);

            throw $exception;
        }
    }

    public function testEmptyResponseBodyDecodesToEmptyArray(): void
    {
        $client = new RecordingClient();
        $client->willReturn(204, '');

        self::assertSame([], $this->transport($client)->request(method: HttpMethod::DELETE, path: 'plans/plan_1'));
    }

    public function testBadRequestStatusThrowsWhopApiException(): void
    {
        $client = new RecordingClient();
        $client->willReturn(400, '{"message":"bad request"}');

        try {
            $this->transport($client)->request(method: HttpMethod::POST, path: 'companies', body: ['x' => 1]);
            self::fail('Expected WhopApiException');
        } catch (WhopApiException $exception) {
            self::assertSame(400, $exception->statusCode);
        }
    }

    public function testTrailingSlashOnBaseUrlAndLeadingSlashOnPathProduceNoDoubleSlash(): void
    {
        $factory = new Psr17Factory();
        $transport = new HttpTransport(
            httpClient: $client = new RecordingClient(),
            apiKey: 'apik_test',
            baseUrl: 'https://api.whop.com/api/v1/',
            requestFactory: $factory,
            streamFactory: $factory,
        );
        $client->willReturn(200, '{}');

        $transport->request(method: HttpMethod::GET, path: '/companies/biz_1');

        self::assertNotNull($client->lastRequest);
        self::assertSame('https://api.whop.com/api/v1/companies/biz_1', (string) $client->lastRequest->getUri());
    }

    public function testInvalidUtf8InBodyThrowsSerializationException(): void
    {
        $client = new RecordingClient();

        try {
            $this->transport($client)->request(method: HttpMethod::POST, path: 'companies', body: ['x' => "\xB1\x31"]);
            self::fail('Expected SerializationException');
        } catch (SerializationException $exception) {
            self::assertStringContainsString('Failed to encode request body as JSON', $exception->getMessage());
            self::assertInstanceOf(WhopException::class, $exception);
        }
    }

    public function testMalformedJsonResponseThrowsSerializationException(): void
    {
        $client = new RecordingClient();
        $client->willReturn(200, 'not json');

        $this->expectException(SerializationException::class);

        try {
            $this->transport($client)->request(method: HttpMethod::GET, path: 'companies/biz_1');
        } catch (SerializationException $exception) {
            self::assertStringContainsString('Failed to decode Whop API response (HTTP 200)', $exception->getMessage());

            throw $exception;
        }
    }

    public function testNonObjectJsonBodyYieldsEmptyArray(): void
    {
        $client = new RecordingClient();
        $client->willReturn(200, '[1,2,3]');

        $result = $this->transport($client)->request(method: HttpMethod::GET, path: 'companies');

        self::assertSame([], $result);
    }
}
