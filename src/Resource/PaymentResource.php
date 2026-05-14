<?php

declare(strict_types=1);

namespace Matchable\Whop\Resource;

use Matchable\Whop\Dto\Payment\Payment;
use Matchable\Whop\Dto\Payment\RefundResponse;
use Matchable\Whop\Dto\Payment\VoidResponse;
use Matchable\Whop\Exception\WhopException;
use Matchable\Whop\Http\HttpMethod;
use Matchable\Whop\Http\HttpTransport;

final readonly class PaymentResource extends BaseResource
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
     * @throws WhopException
     */
    public function create(array $data): array
    {
        return $this->request(method: HttpMethod::POST, url: 'payments', body: $data);
    }

    /**
     * @throws WhopException
     */
    public function get(string $id): Payment
    {
        $decoded = $this->request(method: HttpMethod::GET, url: sprintf('payments/%s', $id));

        return Payment::create($decoded);
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
        return $this->request(method: HttpMethod::GET, url: 'payments', query: $query);
    }

    /**
     * @throws WhopException
     */
    public function refund(string $id, ?int $amount = null): RefundResponse
    {
        $body = [];

        if (null !== $amount) {
            $body['partial_amount'] = $amount;
        }

        $decoded = $this->request(method: HttpMethod::POST, url: sprintf('payments/%s/refund', $id), body: $body);

        return RefundResponse::fromResponse($decoded);
    }

    /**
     * @throws WhopException
     */
    public function void(string $id): VoidResponse
    {
        $decoded = $this->request(method: HttpMethod::POST, url: sprintf('payments/%s/void', $id));

        return VoidResponse::fromResponse($decoded);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopException
     */
    public function getFees(string $id): array
    {
        return $this->request(method: HttpMethod::GET, url: sprintf('payments/%s/fees', $id));
    }

    /**
     * @return array<string, mixed>
     *
     * @throws WhopException
     */
    public function retry(string $id): array
    {
        return $this->request(method: HttpMethod::POST, url: sprintf('payments/%s/retry', $id));
    }
}
