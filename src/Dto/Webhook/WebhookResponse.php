<?php

declare(strict_types=1);

namespace Matchable\Whop\Dto\Webhook;

use Matchable\Whop\Exception\MissingArgumentsException;

final readonly class WebhookResponse
{
    private function __construct(
        public string $id,
        public ?string $url,
        /** @var list<string> */
        public array $events,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromResponse(array $data): self
    {
        $id = $data['id'] ?? null;

        if (!\is_string($id) || '' === $id) {
            throw MissingArgumentsException::forField(context: 'Webhook response', field: 'id');
        }

        $url = $data['url'] ?? null;
        $eventsRaw = $data['events'] ?? [];

        /** @var list<string> $events */
        $events = \is_array($eventsRaw)
            ? array_values(array_filter(array_map(static fn (mixed $e): ?string => \is_string($e) ? $e : null, $eventsRaw)))
            : [];

        return new self(
            id: $id,
            url: \is_string($url) ? $url : null,
            events: $events,
        );
    }
}
