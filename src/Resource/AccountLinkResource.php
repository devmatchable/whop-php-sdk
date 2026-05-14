<?php

declare(strict_types=1);

namespace Matchable\Whop\Resource;

use Matchable\Whop\Dto\AccountLink\AccountLink;
use Matchable\Whop\Exception\WhopException;
use Matchable\Whop\Http\HttpMethod;
use Matchable\Whop\Http\HttpTransport;

final readonly class AccountLinkResource extends BaseResource
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
    public function create(array $data): AccountLink
    {
        $decoded = $this->request(method: HttpMethod::POST, url: 'account_links', body: $data);

        return AccountLink::fromResponse($decoded);
    }
}
