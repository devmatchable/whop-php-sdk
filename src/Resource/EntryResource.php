<?php

declare(strict_types=1);

namespace Matchable\Whop\Resource;

use Matchable\Whop\Exception\WhopApiException;
use Matchable\Whop\Http\HttpMethod;
use Matchable\Whop\Http\HttpTransport;

final readonly class EntryResource extends BaseResource
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
    public function list(array $query = []): array
    {
        return $this->request(method: HttpMethod::GET, url: 'entries', query: $query);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function get(string $id): array
    {
        return $this->request(method: HttpMethod::GET, url: sprintf('entries/%s', $id));
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function approve(string $id): array
    {
        return $this->request(method: HttpMethod::POST, url: sprintf('entries/%s/approve', $id));
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function deny(string $id): array
    {
        return $this->request(method: HttpMethod::POST, url: sprintf('entries/%s/deny', $id));
    }
}
