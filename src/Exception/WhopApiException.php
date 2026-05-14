<?php

declare(strict_types=1);

namespace Matchable\Whop\Exception;

final class WhopApiException extends WhopException
{
    /**
     * @param array<string, mixed> $responseBody
     */
    public function __construct(
        public readonly int $statusCode,
        string $message,
        public readonly array $responseBody = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct(message: $message, code: $statusCode, previous: $previous);
    }

    /**
     * @param array<string, mixed> $responseBody
     */
    public static function fromResponse(int $statusCode, array $responseBody): self
    {
        $errorMessage = 'Unknown Whop API error';

        if (isset($responseBody['message']) && \is_string($responseBody['message'])) {
            $errorMessage = $responseBody['message'];
        } elseif (isset($responseBody['error']) && \is_array($responseBody['error']) && isset($responseBody['error']['message']) && \is_string($responseBody['error']['message'])) {
            $errorMessage = $responseBody['error']['message'];
        }

        return new self(
            statusCode: $statusCode,
            message: sprintf('Whop API error (%d): %s', $statusCode, $errorMessage),
            responseBody: $responseBody,
        );
    }
}
