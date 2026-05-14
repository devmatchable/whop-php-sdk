# AGENTS.md — Whop PHP SDK Integration Guide

A dense reference for AI coding agents wiring `devmatchable/whop-php-sdk` into a project.
Read this in full before generating any integration code.

---

## What this package is

A framework-agnostic PHP 8.4+ client for the Whop API. Pure PSR-18/PSR-17/PSR-7 —
no framework dependency. PHP namespace: `Matchable\Whop\`.

```bash
composer require devmatchable/whop-php-sdk
```

**You must also install a PSR-18 HTTP client and a PSR-7/17 implementation.**
The package does not pull one in. Suggested pairing:

```bash
composer require nyholm/psr7 symfony/http-client
```

---

## Constructing the client

```php
use Matchable\Whop\WhopApiClient;
use Symfony\Component\HttpClient\Psr18Client;

$client = new WhopApiClient(
    httpClient: new Psr18Client(),   // PSR-18 — required
    apiKey: $_ENV['WHOP_API_KEY'],   // Bearer token — required
    // baseUrl: 'https://api.whop.com/api/v1',  // production default; omit normally
    // requestFactory: null,  // auto-discovered via php-http/discovery when null
    // streamFactory: null,   // auto-discovered via php-http/discovery when null
);
```

Full constructor signature (PHP 8.4 named args):

```php
public function __construct(
    ClientInterface $httpClient,
    string $apiKey,
    string $baseUrl = 'https://api.whop.com/api/v1',
    ?RequestFactoryInterface $requestFactory = null,
    ?StreamFactoryInterface $streamFactory = null,
)
```

**Sandbox:** pass `baseUrl: 'https://sandbox-api.whop.com/api/v1'` and a sandbox API key.

If `requestFactory`/`streamFactory` are omitted and no PSR-17 implementation is installed,
the constructor throws `Http\Discovery\Exception\NotFoundException` immediately.

---

## Resource access pattern

```
$client-><resource>-><method>(...)
```

All 57 resource groups are public readonly properties on `WhopApiClient`. Methods return:
- A **typed DTO** when a mapping exists for the endpoint (e.g. `Company`, `Payment`,
  `RefundResponse`, `AccountLink`).
- A raw **`array<string, mixed>`** when no DTO exists (e.g. `list()`, `create()` on some
  resources, `getFees()`, `retry()`).

Per-endpoint method signatures and parameter shapes are in the
[GitHub Wiki](https://github.com/devmatchable/whop-php-sdk/wiki).

Examples:

```php
$company = $client->companies->get('biz_xxxxxxxx');  // Company DTO
$payment = $client->payments->get('pay_xxxxxxxx');   // Payment DTO
$refund  = $client->payments->refund('pay_xxxxxxxx', amount: 500); // RefundResponse DTO
$list    = $client->companies->list(['page' => 1]);  // array
```

---

## Exception model

Every failure is a subtype of `Matchable\Whop\Exception\WhopException`
(extends `\RuntimeException`). Catching `WhopException` is always safe.

| Exception | When |
|---|---|
| `WhopApiException` | HTTP 4xx/5xx from the API. Exposes `->statusCode` (int) and `->responseBody` (array). |
| `TransportException` | Network-level PSR-18 failure (connection refused, DNS, timeout). |
| `SerializationException` | JSON encode failure on the request body, or JSON decode failure on the response. |
| `MissingArgumentsException` | A required field was missing or invalid when hydrating a typed DTO from the response. |
| `WebhookVerificationException` | Webhook signature, timestamp, or required header check failed. Note: does NOT extend `WhopApiException` — it extends `WhopException` directly. |

Recommended catch strategy:

```php
use Matchable\Whop\Exception\WhopApiException;
use Matchable\Whop\Exception\TransportException;
use Matchable\Whop\Exception\WhopException;

try {
    $payment = $client->payments->get('pay_xxxxxxxx');
} catch (WhopApiException $e) {
    // e.g. 404, 422, 429, 500
    $status = $e->statusCode;
    $body   = $e->responseBody; // decoded array
} catch (TransportException $e) {
    // network failure — retry may be appropriate
} catch (WhopException $e) {
    // SerializationException or MissingArgumentsException
}
```

---

## Webhook verification

```php
use Matchable\Whop\Exception\WebhookVerificationException;
use Matchable\Whop\Webhook\WebhookVerifier;

$verifier = new WebhookVerifier(webhookSecret: $_ENV['WHOP_WEBHOOK_SECRET']);

// Option A — raw body + header array (case-insensitive keys):
try {
    $verifier->verify($rawBody, [
        'webhook-id'        => $headers['webhook-id'],
        'webhook-timestamp' => $headers['webhook-timestamp'],
        'webhook-signature' => $headers['webhook-signature'],
    ]);
} catch (WebhookVerificationException $e) {
    http_response_code(400);
    exit;
}

// Option B — PSR-7 RequestInterface:
$verifier->verifyRequest($psr7Request);
```

The verifier enforces a 5-minute timestamp tolerance and handles both `whsec_`
(production, base64-decoded before HMAC) and `ws_` (sandbox, used as-is) secret formats.

---

## Do / don't

**Do:**
- Always pass a concrete PSR-18 client — the package will not boot without one.
- Use named arguments when constructing `WhopApiClient` and `WebhookVerifier`; the
  codebase is written that way and it keeps intent clear.
- Catch `WhopException` (or a specific subtype) — never `\Exception` — when you only
  intend to handle SDK failures.
- Read the raw body into a string before calling `verify()` — once you've consumed the
  PSR-7 stream, `verifyRequest()` will receive an empty body and fail.
- Check the return type in the Wiki before assuming a method returns a DTO; some
  endpoints return plain arrays even for single-resource fetches.

**Don't:**
- Don't omit `requestFactory` / `streamFactory` unless a PSR-17 implementation is
  installed — the constructor throws immediately if discovery fails.
- Don't pass string values containing non-UTF-8 bytes in request body arrays; the JSON
  encoder will throw a `SerializationException`.
- Don't catch `\InvalidArgumentException` for webhook failures — the verifier throws
  `WebhookVerificationException` (a `WhopException` subtype), not the built-in PHP
  invalid-argument exception.
- Don't confuse `$client->refunds` (the standalone Refunds resource) with
  `$client->payments->refund()`; both exist and cover different endpoints.
- Don't assume `list()` returns a typed collection — all list methods return
  `array<string, mixed>` (the raw decoded response, typically with a `data` key).
