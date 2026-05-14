<?php

declare(strict_types=1);

namespace Matchable\Whop\Dto\Payment;

use Matchable\Whop\Exception\MissingArgumentsException;

final readonly class VoidResponse
{
    private function __construct(
        public string $id,
        public ?string $status,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromResponse(array $data): self
    {
        $id = $data['id'] ?? null;

        if (!\is_string($id) || '' === $id) {
            throw MissingArgumentsException::forField(context: 'Void response', field: 'id');
        }

        $status = $data['status'] ?? null;

        return new self(
            id: $id,
            status: \is_string($status) ? $status : null,
        );
    }
}
