<?php

declare(strict_types=1);

namespace Matchable\Whop\Dto\AccountLink;

use Matchable\Whop\Exception\MissingArgumentsException;

final readonly class AccountLink
{
    private function __construct(
        public string $url,
        public string $expiresAt,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromResponse(array $data): self
    {
        $url = $data['url'] ?? null;

        if (!\is_string($url) || '' === $url) {
            throw MissingArgumentsException::forField(context: 'AccountLink response', field: 'url');
        }

        $expiresAt = $data['expires_at'] ?? null;

        if (!\is_string($expiresAt) || '' === $expiresAt) {
            throw MissingArgumentsException::forField(context: 'AccountLink response', field: 'expires_at');
        }

        return new self(
            url: $url,
            expiresAt: $expiresAt,
        );
    }
}
