<?php

declare(strict_types=1);

namespace Matchable\Whop\Dto\Checkout;

use Matchable\Whop\Exception\MissingArgumentsException;

final readonly class CheckoutConfigurationResponse
{
    /**
     * @param array<string, mixed> $metadata
     */
    private function __construct(
        public string $id,
        public string $purchaseUrl,
        public ?string $planId,
        public ?string $status,
        public ?string $currency,
        public ?int $finalPriceAmount,
        public array $metadata,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromResponse(array $data): self
    {
        $id = $data['id'] ?? null;

        if (!\is_string($id) || '' === $id) {
            throw MissingArgumentsException::forField(context: 'Checkout response', field: 'id');
        }

        $purchaseUrl = $data['purchase_url'] ?? $data['url'] ?? null;

        if (!\is_string($purchaseUrl) || '' === $purchaseUrl) {
            throw MissingArgumentsException::forField(context: 'Checkout response', field: 'purchase_url');
        }

        $plan = $data['plan'] ?? null;
        $planIdRaw = (\is_array($plan) ? ($plan['id'] ?? null) : null) ?? $data['plan_id'] ?? null;
        $status = $data['status'] ?? null;
        $currency = $data['currency'] ?? null;
        $finalPriceAmount = $data['final_price_amount'] ?? null;
        $metadataRaw = $data['metadata'] ?? [];

        return new self(
            id: $id,
            purchaseUrl: $purchaseUrl,
            planId: \is_string($planIdRaw) ? $planIdRaw : null,
            status: \is_string($status) ? $status : null,
            currency: \is_string($currency) ? $currency : null,
            finalPriceAmount: \is_int($finalPriceAmount) ? $finalPriceAmount : null,
            metadata: self::toStringKeyedArray($metadataRaw),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function toStringKeyedArray(mixed $value): array
    {
        if (!\is_array($value)) {
            return [];
        }

        $result = [];

        foreach ($value as $k => $v) {
            if (\is_string($k)) {
                $result[$k] = $v;
            }
        }

        return $result;
    }
}
