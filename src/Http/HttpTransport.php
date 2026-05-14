<?php

declare(strict_types=1);

namespace Matchable\Whop\Http;

use Matchable\Whop\Exception\SerializationException;
use Matchable\Whop\Exception\TransportException;
use Matchable\Whop\Exception\WhopApiException;
use Matchable\Whop\Exception\WhopException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class HttpTransport
{
    public function __construct(
        private ClientInterface $httpClient,
        private string $apiKey,
        private string $baseUrl,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopException
     */
    private function decodeJson(string $content, int $statusCode): array
    {
        if ('' === $content) {
            return [];
        }

        try {
            $decoded = json_decode(json: $content, associative: true, depth: 512, flags: \JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new SerializationException(
                message: sprintf('Failed to decode Whop API response (HTTP %d): %s', $statusCode, $exception->getMessage()),
                previous: $exception,
            );
        }

        if (!\is_array($decoded)) {
            return [];
        }

        $result = [];
        foreach ($decoded as $key => $value) {
            if (\is_string($key)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     *
     * @throws WhopException
     */
    public function request(string $method, string $path, array $query = [], array $body = []): array
    {
        $uri = rtrim($this->baseUrl, '/').'/'.ltrim($path, '/');

        if ([] !== $query) {
            $uri .= '?'.http_build_query($query);
        }

        $request = $this->requestFactory->createRequest($method, $uri)
            ->withHeader('Authorization', sprintf('Bearer %s', $this->apiKey))
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json');

        if ([] !== $body) {
            try {
                $encoded = json_encode(value: $body, flags: \JSON_THROW_ON_ERROR);
            } catch (\JsonException $exception) {
                throw new SerializationException(
                    message: sprintf('Failed to encode request body as JSON: %s', $exception->getMessage()),
                    previous: $exception,
                );
            }

            $request = $request->withBody($this->streamFactory->createStream($encoded));
        }

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw new TransportException(
                message: sprintf('Whop API request failed: %s', $exception->getMessage()),
                previous: $exception,
            );
        }

        $statusCode = $response->getStatusCode();
        $content = (string) $response->getBody();

        $decoded = $this->decodeJson($content, $statusCode);

        if ($statusCode >= 400) {
            throw WhopApiException::fromResponse(statusCode: $statusCode, responseBody: $decoded);
        }

        return $decoded;
    }
}
