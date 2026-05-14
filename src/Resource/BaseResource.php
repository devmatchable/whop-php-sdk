<?php

declare(strict_types=1);

namespace Matchable\Whop\Resource;

use Matchable\Whop\Exception\WhopApiException;
use Matchable\Whop\Http\HttpTransport;

abstract readonly class BaseResource
{
    protected function __construct(
        private HttpTransport $transport,
    ) {
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    protected function request(string $method, string $url, array $query = [], array $body = []): array
    {
        return $this->transport->request(method: $method, path: $url, query: $query, body: $body);
    }
}
