<?php

declare(strict_types=1);

namespace Matchable\Whop\Dto\Payment;

use Matchable\Whop\Exception\MissingArgumentsException;

final readonly class RefundResponse
{
    private function __construct(
        public string $refundId,
        public ?string $paymentId,
        public ?string $status,
        public ?int $amount,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromResponse(array $data): self
    {
        $refundId = $data['refund_id'] ?? $data['id'] ?? null;

        if (!\is_string($refundId) || '' === $refundId) {
            throw new MissingArgumentsException('Refund response is missing required field "refund_id" or "id".');
        }

        $paymentId = $data['payment_id'] ?? null;
        $status = $data['status'] ?? null;
        $amount = $data['amount'] ?? null;

        return new self(
            refundId: $refundId,
            paymentId: \is_string($paymentId) ? $paymentId : null,
            status: \is_string($status) ? $status : null,
            amount: \is_int($amount) ? $amount : null,
        );
    }
}
