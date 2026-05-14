<?php

declare(strict_types=1);

namespace Matchable\Whop\Resource;

use Matchable\Whop\Exception\WhopApiException;
use Matchable\Whop\Http\HttpMethod;
use Matchable\Whop\Http\HttpTransport;

final readonly class MembershipResource extends BaseResource
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
        return $this->request(method: HttpMethod::GET, url: 'memberships', query: $query);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function get(string $id): array
    {
        return $this->request(method: HttpMethod::GET, url: sprintf('memberships/%s', $id));
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function update(string $id, array $data): array
    {
        return $this->request(method: HttpMethod::PATCH, url: sprintf('memberships/%s', $id), body: $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function addFreeDays(string $id, array $data): array
    {
        return $this->request(method: HttpMethod::POST, url: sprintf('memberships/%s/add-free-days', $id), body: $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function cancel(string $id, array $data = []): array
    {
        return $this->request(method: HttpMethod::POST, url: sprintf('memberships/%s/cancel', $id), body: $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function pause(string $id, array $data = []): array
    {
        return $this->request(method: HttpMethod::POST, url: sprintf('memberships/%s/pause', $id), body: $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function resume(string $id, array $data = []): array
    {
        return $this->request(method: HttpMethod::POST, url: sprintf('memberships/%s/resume', $id), body: $data);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function uncancel(string $id): array
    {
        return $this->request(method: HttpMethod::POST, url: sprintf('memberships/%s/uncancel', $id));
    }
}
