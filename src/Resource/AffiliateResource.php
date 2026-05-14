<?php

declare(strict_types=1);

namespace Matchable\Whop\Resource;

use Matchable\Whop\Exception\WhopApiException;
use Matchable\Whop\Http\HttpMethod;
use Matchable\Whop\Http\HttpTransport;

final readonly class AffiliateResource extends BaseResource
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
        return $this->request(method: HttpMethod::POST, url: 'affiliates', body: $data);
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
        return $this->request(method: HttpMethod::GET, url: 'affiliates', query: $query);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function get(string $id): array
    {
        return $this->request(method: HttpMethod::GET, url: sprintf('affiliates/%s', $id));
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function archive(string $id): array
    {
        return $this->request(method: HttpMethod::POST, url: sprintf('affiliates/%s/archive', $id));
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function unarchive(string $id): array
    {
        return $this->request(method: HttpMethod::POST, url: sprintf('affiliates/%s/unarchive', $id));
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function createOverride(string $id, array $data): array
    {
        return $this->request(method: HttpMethod::POST, url: sprintf('affiliates/%s/overrides', $id), body: $data);
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function listOverrides(string $id, array $query = []): array
    {
        return $this->request(method: HttpMethod::GET, url: sprintf('affiliates/%s/overrides', $id), query: $query);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function getOverride(string $id, string $overrideId): array
    {
        return $this->request(method: HttpMethod::GET, url: sprintf('affiliates/%s/overrides/%s', $id, $overrideId));
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function updateOverride(string $id, string $overrideId, array $data): array
    {
        return $this->request(method: HttpMethod::PATCH, url: sprintf('affiliates/%s/overrides/%s', $id, $overrideId), body: $data);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function deleteOverride(string $id, string $overrideId): array
    {
        return $this->request(method: HttpMethod::DELETE, url: sprintf('affiliates/%s/overrides/%s', $id, $overrideId));
    }
}
