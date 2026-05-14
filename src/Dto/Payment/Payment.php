<?php

declare(strict_types=1);

namespace Matchable\Whop\Dto\Payment;

use Matchable\Whop\Exception\MissingArgumentsException;

final readonly class Payment
{
    /**
     * @param array<string, mixed> $metadata
     */
    private function __construct(
        public string $id,
        public string $status,
        public ?string $subStatus,
        public int $amount,
        public string $currency,
        public ?int $applicationFee,
        public array $metadata,
        public ?string $createdAt,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function create(array $data): self
    {
        $id = $data['id'] ?? null;

        if (!\is_string($id) || '' === $id) {
            throw MissingArgumentsException::forField(context: 'Payment response', field: 'id');
        }

        $status = $data['status'] ?? null;

        if (!\is_string($status) || '' === $status) {
            throw MissingArgumentsException::forField(context: 'Payment response', field: 'status');
        }

        $amount = $data['amount'] ?? null;

        if (!\is_int($amount)) {
            throw MissingArgumentsException::forField(context: 'Payment response', field: 'amount');
        }

        $currencyRaw = $data['currency'] ?? null;

        if (!\is_string($currencyRaw) || '' === $currencyRaw) {
            throw MissingArgumentsException::forField(context: 'Payment response', field: 'currency');
        }

        $subStatus = $data['sub_status'] ?? null;
        $applicationFee = $data['application_fee'] ?? $data['application_fee_amount'] ?? null;
        $metadataRaw = $data['metadata'] ?? [];
        $createdAt = $data['created_at'] ?? null;

        return new self(
            id: $id,
            status: $status,
            subStatus: \is_string($subStatus) ? $subStatus : null,
            amount: $amount,
            currency: strtoupper($currencyRaw),
            applicationFee: \is_int($applicationFee) ? $applicationFee : null,
            metadata: self::toStringKeyedArray($metadataRaw),
            createdAt: \is_string($createdAt) ? $createdAt : null,
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
