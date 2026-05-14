<?php

declare(strict_types=1);

namespace Matchable\Whop\Resource;

use Matchable\Whop\Exception\WhopApiException;
use Matchable\Whop\Http\HttpMethod;
use Matchable\Whop\Http\HttpTransport;

final readonly class DisputeResource extends BaseResource
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
        return $this->request(method: HttpMethod::GET, url: 'disputes', query: $query);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function get(string $id): array
    {
        return $this->request(method: HttpMethod::GET, url: sprintf('disputes/%s', $id));
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function updateEvidence(string $id, array $data): array
    {
        return $this->request(method: HttpMethod::PATCH, url: sprintf('disputes/%s/evidence', $id), body: $data);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopApiException
     */
    public function submitEvidence(string $id): array
    {
        return $this->request(method: HttpMethod::POST, url: sprintf('disputes/%s/submit-evidence', $id));
    }
}
