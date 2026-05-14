<?php

declare(strict_types=1);

namespace Matchable\Whop\Dto\Company;

use Matchable\Whop\Exception\MissingArgumentsException;

final readonly class Company
{
    private function __construct(
        public string $id,
        public string $name,
        public ?string $email,
        public ?string $country,
        public ?string $onboardingUrl,
        public bool $verified,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function create(array $data): self
    {
        $id = $data['id'] ?? null;

        if (!\is_string($id) || '' === $id) {
            throw MissingArgumentsException::forField(context: 'Company response', field: 'id');
        }

        $name = $data['title'] ?? $data['name'] ?? null;

        if (!\is_string($name) || '' === $name) {
            throw MissingArgumentsException::forField(context: 'Company response', field: 'title');
        }

        $email = $data['email'] ?? null;
        $country = $data['country'] ?? null;
        $onboardingUrl = $data['onboarding_url'] ?? null;
        $verified = $data['verified'] ?? false;

        return new self(
            id: $id,
            name: $name,
            email: \is_string($email) ? $email : null,
            country: \is_string($country) ? $country : null,
            onboardingUrl: \is_string($onboardingUrl) ? $onboardingUrl : null,
            verified: (bool) $verified,
        );
    }
}
