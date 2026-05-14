<?php

declare(strict_types=1);

namespace Matchable\Whop\Resource;

use Matchable\Whop\Exception\WhopApiException;
use Matchable\Whop\Http\HttpMethod;
use Matchable\Whop\Http\HttpTransport;

final readonly class ExperienceResource extends BaseResource
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
        return $this->request(method: HttpMethod::POST, url: 'experiences', body: $data);
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
        return $this->request(method: HttpMethod::GET, url: 'experiences', query: $query);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function get(string $id): array
    {
        return $this->request(method: HttpMethod::GET, url: sprintf('experiences/%s', $id));
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
        return $this->request(method: HttpMethod::PATCH, url: sprintf('experiences/%s', $id), body: $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function attach(string $id, array $data): array
    {
        return $this->request(method: HttpMethod::POST, url: sprintf('experiences/%s/attach', $id), body: $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function detach(string $id, array $data): array
    {
        return $this->request(method: HttpMethod::POST, url: sprintf('experiences/%s/detach', $id), body: $data);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function duplicate(string $id): array
    {
        return $this->request(method: HttpMethod::POST, url: sprintf('experiences/%s/duplicate', $id));
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function delete(string $id): array
    {
        return $this->request(method: HttpMethod::DELETE, url: sprintf('experiences/%s', $id));
    }
}
