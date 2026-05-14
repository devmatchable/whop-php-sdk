<?php

declare(strict_types=1);

namespace Matchable\Whop\Resource;

use Matchable\Whop\Exception\WhopApiException;
use Matchable\Whop\Http\HttpMethod;
use Matchable\Whop\Http\HttpTransport;

final readonly class AdResource extends BaseResource
{
    public static function initiate(HttpTransport $transport): self
    {
        return new self(transport: $transport);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function create(array $data): array
    {
        return $this->request(method: HttpMethod::POST, url: 'ads', body: $data);
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
        return $this->request(method: HttpMethod::GET, url: 'ads', query: $query);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function get(string $id): array
    {
        return $this->request(method: HttpMethod::GET, url: sprintf('ads/%s', $id));
    }
}
