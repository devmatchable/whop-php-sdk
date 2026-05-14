<?php

declare(strict_types=1);

namespace Matchable\Whop\Tests\Webhook;

use Matchable\Whop\Exception\WebhookVerificationException;
use Matchable\Whop\Exception\WhopException;
use Matchable\Whop\Webhook\WebhookVerifier;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class WebhookVerifierTest extends TestCase
{
    private const string SECRET = 'ws_testsecret';

    /**
     * @return array{0: string, 1: array<string, string>}
     */
    private function signedPayload(string $secret = self::SECRET, ?int $timestamp = null): array
    {
        $payload = '{"event":"payment.created"}';
        $id = 'msg_1';
        $ts = (string) ($timestamp ?? time());
        $signed = sprintf('%s.%s.%s', $id, $ts, $payload);
        $signature = base64_encode(hash_hmac(algo: 'sha256', data: $signed, key: $secret, binary: true));

        return [$payload, [
            'webhook-id' => $id,
            'webhook-timestamp' => $ts,
            'webhook-signature' => 'v1,'.$signature,
        ]];
    }

    public function testValidSignaturePasses(): void
    {
        [$payload, $headers] = $this->signedPayload();

        (new WebhookVerifier(self::SECRET))->verify($payload, $headers);

        $this->expectNotToPerformAssertions();
    }

    public function testHeaderLookupIsCaseInsensitive(): void
    {
        [$payload, $headers] = $this->signedPayload();
        $upper = array_combine(array_map('strtoupper', array_keys($headers)), array_values($headers));

        (new WebhookVerifier(self::SECRET))->verify($payload, $upper);

        $this->expectNotToPerformAssertions();
    }

    public function testBadSignatureThrows(): void
    {
        [$payload, $headers] = $this->signedPayload();
        $headers['webhook-signature'] = 'v1,'.base64_encode('wrong');

        $this->expectException(WebhookVerificationException::class);
        (new WebhookVerifier(self::SECRET))->verify($payload, $headers);
    }

    public function testStaleTimestampThrows(): void
    {
        [$payload, $headers] = $this->signedPayload(timestamp: time() - 1000);

        $this->expectException(WebhookVerificationException::class);
        (new WebhookVerifier(self::SECRET))->verify($payload, $headers);
    }

    public function testMissingHeaderThrows(): void
    {
        [$payload, $headers] = $this->signedPayload();
        unset($headers['webhook-signature']);

        $this->expectException(WebhookVerificationException::class);
        (new WebhookVerifier(self::SECRET))->verify($payload, $headers);
    }

    /**
     * @param array<string, string> $headers
     */
    #[DataProvider('missingHeaderProvider')]
    public function testEachMissingHeaderThrowsIndividually(array $headers): void
    {
        [$payload] = $this->signedPayload();

        $this->expectException(WebhookVerificationException::class);
        (new WebhookVerifier(self::SECRET))->verify($payload, $headers);
    }

    /**
     * @return array<string, array{0: array<string, string>}>
     */
    public static function missingHeaderProvider(): array
    {
        $base = [
            'webhook-id' => 'msg_1',
            'webhook-timestamp' => (string) time(),
            'webhook-signature' => 'v1,'.base64_encode('sig'),
        ];

        return [
            'missing webhook-id' => [array_diff_key($base, ['webhook-id' => true])],
            'missing webhook-timestamp' => [array_diff_key($base, ['webhook-timestamp' => true])],
            'missing webhook-signature' => [array_diff_key($base, ['webhook-signature' => true])],
        ];
    }

    public function testTimestampExactlyAtToleranceBoundaryPasses(): void
    {
        [$payload, $headers] = $this->signedPayload(timestamp: time() - 300);

        (new WebhookVerifier(self::SECRET))->verify($payload, $headers);

        $this->expectNotToPerformAssertions();
    }

    public function testTimestampOneSecondBeyondToleranceThrows(): void
    {
        [$payload, $headers] = $this->signedPayload(timestamp: time() - 301);

        $this->expectException(WebhookVerificationException::class);
        (new WebhookVerifier(self::SECRET))->verify($payload, $headers);
    }

    public function testWhsecWithInvalidBase64Throws(): void
    {
        $verifier = new WebhookVerifier('whsec_!!!not-valid-base64!!!');
        [$payload, $headers] = $this->signedPayload();

        $this->expectException(WebhookVerificationException::class);
        $verifier->verify($payload, $headers);
    }

    public function testWhsecSecretIsBase64Decoded(): void
    {
        $rawSecret = 'supersecretkey';
        $secret = 'whsec_'.base64_encode($rawSecret);
        $payload = '{"event":"x"}';
        $id = 'msg_2';
        $ts = (string) time();
        $signature = base64_encode(hash_hmac(algo: 'sha256', data: sprintf('%s.%s.%s', $id, $ts, $payload), key: $rawSecret, binary: true));

        (new WebhookVerifier($secret))->verify($payload, [
            'webhook-id' => $id,
            'webhook-timestamp' => $ts,
            'webhook-signature' => 'v1,'.$signature,
        ]);

        $this->expectNotToPerformAssertions();
    }

    public function testVerifyRequestExtractsHeadersAndBody(): void
    {
        [$payload, $headers] = $this->signedPayload();
        $factory = new Psr17Factory();
        $request = $factory->createRequest('POST', 'https://example.test/webhook')
            ->withBody($factory->createStream($payload));
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        (new WebhookVerifier(self::SECRET))->verifyRequest($request);

        $this->expectNotToPerformAssertions();
    }

    public function testNonNumericTimestampThrowsWebhookVerificationException(): void
    {
        [$payload, $headers] = $this->signedPayload();
        $headers['webhook-timestamp'] = 'not-a-number';

        $this->expectException(WebhookVerificationException::class);
        $this->expectExceptionMessage('Webhook timestamp header is not a valid Unix timestamp.');
        (new WebhookVerifier(self::SECRET))->verify($payload, $headers);
    }

    public function testWebhookVerificationExceptionIsWhopException(): void
    {
        $exception = new WebhookVerificationException(message: 'bad signature');

        self::assertInstanceOf(WhopException::class, $exception);
    }
}
