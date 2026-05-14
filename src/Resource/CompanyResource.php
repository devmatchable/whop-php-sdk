<?php

declare(strict_types=1);

namespace Matchable\Whop\Resource;

use Matchable\Whop\Dto\Company\Company;
use Matchable\Whop\Exception\WhopException;
use Matchable\Whop\Http\HttpMethod;
use Matchable\Whop\Http\HttpTransport;

final readonly class CompanyResource extends BaseResource
{
    public static function initiate(HttpTransport $transport): self
    {
        return new self(transport: $transport);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws WhopException
     */
    public function create(array $data): Company
    {
        $decoded = $this->request(method: HttpMethod::POST, url: 'companies', body: $data);

        return Company::create($decoded);
    }

    /**
     * @throws WhopException
     */
    public function get(string $id): Company
    {
        $decoded = $this->request(method: HttpMethod::GET, url: sprintf('companies/%s', $id));

        return Company::create($decoded);
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     *
     * @throws WhopException
     */
    public function list(array $query = []): array
    {
        return $this->request(method: HttpMethod::GET, url: 'companies', query: $query);
    }
}
