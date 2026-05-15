# Whop PHP SDK

[![CI](https://github.com/devmatchable/whop-php-sdk/actions/workflows/ci.yml/badge.svg)](https://github.com/devmatchable/whop-php-sdk/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-2a6496)](https://phpstan.org/)

> [!WARNING]
> **This package is in active development and is not yet ready for production use.**
> The public API may change at any time before version `1.0.0` is released.
> Please do not depend on it in production projects until a stable release is published.

Framework-agnostic PHP client for the [Whop](https://whop.com) API. Built on pure PSR
interfaces (PSR-18 / PSR-17 / PSR-7) — drop it into any PHP 8.4+ project without
pulling in a framework or opinionated HTTP stack.

## Requirements

- PHP 8.4+
- A PSR-18 HTTP client and a PSR-7/17 implementation (your choice — see below)

## Installation

```bash
composer require devmatchable/whop-php-sdk
```

The SDK declares only the PSR interface packages and `php-http/discovery` in its
`require` block. Concrete HTTP implementations are not bundled — you pick what fits
your stack.

**Recommended:** `nyholm/psr7` (PSR-7 + PSR-17) paired with `symfony/http-client`
(PSR-18) work well together:

```bash
composer require nyholm/psr7 symfony/http-client
```

Any other PSR-18 client (`guzzlehttp/guzzle`, etc.) and any PSR-7/17 implementation
work as drop-in replacements.

> [!NOTE]
> The SDK uses `Symfony\Component\HttpClient\Psr18Client` — the PSR-18 adapter shipped
> by `symfony/http-client`. This is intentionally different from Symfony's framework-native
> `HttpClientInterface`, which has a non-PSR shape and isn't compatible with this SDK (or
> any other PSR-18 consumer). The two interfaces ship from the same package but solve
> different problems: `HttpClientInterface` is for Symfony-native consumers; `Psr18Client`
> bridges the same underlying client to the PSR-18 standard the SDK type-hints against.

## Quick start

```php
use Matchable\Whop\WhopApiClient;
use Symfony\Component\HttpClient\Psr18Client;

$client = new WhopApiClient(
    httpClient: new Psr18Client(),
    apiKey: $_ENV['WHOP_API_KEY'],
);

// Methods that map to a typed DTO return it directly:
$company = $client->companies->get('biz_xxxxxxxx');
echo $company->name;

// Methods without a DTO return the decoded response array:
$list = $client->companies->list(['page' => 1]);
```

PSR-17 request and stream factories are auto-discovered at construction time via
`php-http/discovery` when you omit them. Pass `requestFactory:` and `streamFactory:`
explicitly if you want to control which implementation is used.

## Error handling

Every failure surfaces as a `WhopException` — network problems, non-2xx responses,
bad JSON, and shape mismatches all go through the same base type, so a single catch
covers everything:

```php
use Matchable\Whop\Exception\WhopException;

try {
    $payment = $client->payments->get('pay_xxxxxxxx');
} catch (WhopException $e) {
    // always safe to catch here
    echo $e->getMessage();
}
```

When you need to distinguish the failure mode, catch a specific subtype:

```php
use Matchable\Whop\Exception\WhopApiException;
use Matchable\Whop\Exception\TransportException;
use Matchable\Whop\Exception\WhopException;

try {
    $payment = $client->payments->get('pay_xxxxxxxx');
} catch (WhopApiException $e) {
    // Non-2xx response from the Whop API
    echo $e->statusCode;       // int — HTTP status code
    print_r($e->responseBody); // array — decoded response body
} catch (TransportException $e) {
    // Network-level failure (DNS, connection refused, etc.)
    echo $e->getMessage();
} catch (WhopException $e) {
    // Anything else: SerializationException, MissingArgumentsException
    echo $e->getMessage();
}
```

The five concrete exception types:

| Exception | When it's thrown |
|---|---|
| `WhopApiException` | The API returned an HTTP 4xx or 5xx response |
| `TransportException` | A network-level PSR-18 failure (connection refused, timeout, etc.) |
| `SerializationException` | The request body couldn't be JSON-encoded, or the response body couldn't be decoded |
| `MissingArgumentsException` | A required field was absent or invalid when hydrating a typed DTO |
| `WebhookVerificationException` | Webhook signature, timestamp, or header verification failed |

`WhopApiException` exposes two public readonly properties: `statusCode` (int) and
`responseBody` (array).

## Configuration

The full constructor signature:

```php
public function __construct(
    ClientInterface $httpClient,           // PSR-18 client — required
    string $apiKey,                        // Whop API key — required
    string $baseUrl = 'https://api.whop.com/api/v1',  // production default
    ?RequestFactoryInterface $requestFactory = null,   // auto-discovered when null
    ?StreamFactoryInterface $streamFactory = null,     // auto-discovered when null
)
```

To target the Whop sandbox instead of production, pass the sandbox base URL:

```php
$client = new WhopApiClient(
    httpClient: new Psr18Client(),
    apiKey: $_ENV['WHOP_SANDBOX_API_KEY'],
    baseUrl: 'https://sandbox-api.whop.com/api/v1',
);
```

If `requestFactory` or `streamFactory` is omitted and no PSR-17 implementation is
installed, the constructor throws `Http\Discovery\Exception\NotFoundException` at
construction time.

## Webhook verification

`WebhookVerifier` implements the [Standard Webhooks](https://www.standardwebhooks.com/)
specification. Both `whsec_` (production) and `ws_` (sandbox) secret formats are
supported — construct it with whichever secret is configured in your Whop dashboard:

```php
use Matchable\Whop\Exception\WebhookVerificationException;
use Matchable\Whop\Webhook\WebhookVerifier;

$verifier = new WebhookVerifier(webhookSecret: $_ENV['WHOP_WEBHOOK_SECRET']);

// From raw body and headers:
try {
    $verifier->verify($rawBody, $requestHeaders);
} catch (WebhookVerificationException $e) {
    // signature mismatch, stale timestamp, or missing headers
    http_response_code(400);
    exit;
}

// From a PSR-7 request object:
$verifier->verifyRequest($psr7Request);
```

`$requestHeaders` is a case-insensitive array mapping header names to values. The
verifier checks for `webhook-id`, `webhook-timestamp`, and `webhook-signature` and
enforces a 5-minute timestamp tolerance.

## Resources

Access the API through resource properties on `$client`. Each method returns a typed
DTO where one exists, otherwise the raw decoded array:

```php
$company  = $client->companies->get('biz_xxxxxxxx');    // Company DTO
$payment  = $client->payments->get('pay_xxxxxxxx');     // Payment DTO
$refund   = $client->payments->refund('pay_xxxxxxxx');  // RefundResponse DTO
$list     = $client->payments->list(['page' => 1]);     // array
```

The SDK covers the full Whop API surface across 57 resource groups. Per-endpoint
method signatures and response shapes are documented in the
[GitHub Wiki](https://github.com/devmatchable/whop-php-sdk/wiki).

A quick map of what's available:

| Group | Properties |
|---|---|
| Core | `$companies`, `$accountLinks`, `$files`, `$accessTokens`, `$authorizedUsers`, `$users`, `$members`, `$webhooks` |
| Payments & Billing | `$payments`, `$checkouts`, `$plans`, `$products`, `$memberships`, `$refunds`, `$invoices`, `$promoCodes`, `$paymentMethods`, `$setupIntents` |
| Platform & Finance | `$transfers`, `$feeMarkups`, `$topups`, `$withdrawals`, `$ledgerAccounts`, `$payoutAccounts`, `$payoutMethods` |
| Disputes | `$disputes`, `$disputeAlerts`, `$resolutionCenter` |
| Commerce | `$shipments`, `$leads`, `$entries`, `$reviews`, `$affiliates`, `$stats` |
| Experiences & Courses | `$experiences`, `$courses`, `$courseChapters`, `$courseLessons`, `$courseLessonInteractions`, `$courseStudents` |
| Communication | `$chatChannels`, `$dmChannels`, `$dmMembers`, `$messages`, `$reactions`, `$forums`, `$forumPosts`, `$supportChannels`, `$notifications` |
| Advertising | `$adCampaigns`, `$adGroups`, `$ads` |
| Apps & AI | `$apps`, `$appBuilds`, `$aiChats`, `$tokenTransactions` |
| Verifications | `$verifications` |

## Development

```bash
composer test       # PHPUnit
composer stan       # PHPStan
composer cs         # PHP-CS-Fixer dry-run
composer cs:fix     # PHP-CS-Fixer apply
composer infection  # Infection mutation testing
```

## License

MIT — see [LICENSE](LICENSE).
