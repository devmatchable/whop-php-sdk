<?php

declare(strict_types=1);

namespace Matchable\Whop\Resource;

use Matchable\Whop\Dto\Checkout\CheckoutConfigurationResponse;
use Matchable\Whop\Exception\WhopException;
use Matchable\Whop\Http\HttpMethod;
use Matchable\Whop\Http\HttpTransport;

final readonly class CheckoutResource extends BaseResource
{
    private const string CHECKOUT_CONFIG = 'checkout_configurations';

    public static function initiate(HttpTransport $transport): self
    {
        return new self(transport: $transport);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws WhopException
     */
    public function create(array $data): CheckoutConfigurationResponse
    {
        $decoded = $this->request(method: HttpMethod::POST, url: self::CHECKOUT_CONFIG, body: $data);

        return CheckoutConfigurationResponse::fromResponse($decoded);
    }

    /**
     * @throws WhopException
     */
    public function get(string $id): CheckoutConfigurationResponse
    {
        $decoded = $this->request(method: HttpMethod::GET, url: sprintf('%s/%s', self::CHECKOUT_CONFIG, $id));

        return CheckoutConfigurationResponse::fromResponse($decoded);
    }
}
