<?php

declare(strict_types=1);

namespace Matchable\Whop\Webhook;

use Matchable\Whop\Exception\WebhookVerificationException;
use Psr\Http\Message\RequestInterface;

final readonly class WebhookVerifier
{
    private const int TIMESTAMP_TOLERANCE_SECONDS = 300;
    private const string SIGNATURE_PREFIX = 'v1,';

    public function __construct(
        private string $webhookSecret,
    ) {
    }

    /**
     * Verify a webhook using the Standard Webhooks specification.
     *
     * @param array<string, string> $headers case-insensitive map containing webhook-id,
     *                                        webhook-timestamp and webhook-signature
     *
     * @throws WebhookVerificationException if verification fails
     */
    public function verify(string $payload, array $headers): void
    {
        $headers = array_change_key_case($headers);
        $webhookId = $headers['webhook-id'] ?? null;
        $webhookTimestamp = $headers['webhook-timestamp'] ?? null;
        $webhookSignature = $headers['webhook-signature'] ?? null;

        if (null === $webhookId || null === $webhookTimestamp || null === $webhookSignature
            || '' === $webhookId || '' === $webhookTimestamp || '' === $webhookSignature) {
            throw new WebhookVerificationException('Missing required webhook headers (webhook-id, webhook-timestamp, webhook-signature).');
        }

        if (!is_numeric($webhookTimestamp)) {
            throw new WebhookVerificationException('Webhook timestamp header is not a valid Unix timestamp.');
        }

        $this->verifyTimestamp((int) $webhookTimestamp);

        $signedContent = sprintf('%s.%s.%s', $webhookId, $webhookTimestamp, $payload);
        $expectedSignature = base64_encode(
            hash_hmac(algo: 'sha256', data: $signedContent, key: $this->getDecodedSecret(), binary: true)
        );

        $this->verifySignature($webhookSignature, $expectedSignature);
    }

    /**
     * @throws WebhookVerificationException if verification fails
     */
    public function verifyRequest(RequestInterface $request): void
    {
        $headers = [];
        foreach (['webhook-id', 'webhook-timestamp', 'webhook-signature'] as $name) {
            if ($request->hasHeader($name)) {
                $headers[$name] = $request->getHeaderLine($name);
            }
        }

        $this->verify((string) $request->getBody(), $headers);
    }

    private function verifyTimestamp(int $timestamp): void
    {
        $difference = abs(time() - $timestamp);

        if ($difference > self::TIMESTAMP_TOLERANCE_SECONDS) {
            throw new WebhookVerificationException(
                sprintf('Webhook timestamp is too old or too new (difference: %d seconds).', $difference)
            );
        }
    }

    private function verifySignature(string $signatureHeader, string $expectedSignature): void
    {
        foreach (explode(' ', $signatureHeader) as $signature) {
            $sigValue = str_starts_with($signature, self::SIGNATURE_PREFIX)
                ? substr($signature, \strlen(self::SIGNATURE_PREFIX))
                : $signature;

            if (hash_equals($expectedSignature, $sigValue)) {
                return;
            }
        }

        throw new WebhookVerificationException('Webhook signature verification failed.');
    }

    private function getDecodedSecret(): string
    {
        $secret = $this->webhookSecret;

        if (str_starts_with($secret, 'whsec_')) {
            $decoded = base64_decode(string: substr($secret, \strlen('whsec_')), strict: true);

            if (false === $decoded) {
                throw new WebhookVerificationException('Invalid webhook secret: unable to base64 decode.');
            }

            return $decoded;
        }

        // Sandbox secrets use the ws_ prefix and are used as-is for HMAC.
        return $secret;
    }
}
