<?php

declare(strict_types=1);

namespace Matchable\Whop\Resource;

use Matchable\Whop\Exception\WhopApiException;
use Matchable\Whop\Http\HttpMethod;
use Matchable\Whop\Http\HttpTransport;

final readonly class StatsResource extends BaseResource
{
    public static function initiate(HttpTransport $transport): self
    {
        return new self(transport: $transport);
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function describe(array $query = []): array
    {
        return $this->request(method: HttpMethod::GET, url: 'stats/describe', query: $query);
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function metric(array $query): array
    {
        return $this->request(method: HttpMethod::GET, url: 'stats/metric', query: $query);
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function raw(array $query): array
    {
        return $this->request(method: HttpMethod::GET, url: 'stats/raw', query: $query);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function sql(array $data): array
    {
        return $this->request(method: HttpMethod::POST, url: 'stats/sql', body: $data);
    }
}
