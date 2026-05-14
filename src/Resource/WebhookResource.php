<?php

declare(strict_types=1);

namespace Matchable\Whop\Resource;

use Matchable\Whop\Dto\Webhook\WebhookResponse;
use Matchable\Whop\Exception\WhopException;
use Matchable\Whop\Http\HttpMethod;
use Matchable\Whop\Http\HttpTransport;

final readonly class WebhookResource extends BaseResource
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
    public function create(array $data): WebhookResponse
    {
        $decoded = $this->request(method: HttpMethod::POST, url: 'webhooks', body: $data);

        return WebhookResponse::fromResponse($decoded);
    }
}
