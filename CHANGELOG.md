# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.0.1] - 2026-05-15

Initial release — a framework-agnostic PHP SDK for the Whop API, extracted from the
Matchable backend.

### Added

- `WhopApiClient` facade exposing the full Whop API across 57 resource groups as
  `public readonly` properties (PHP namespace `Matchable\Whop`).
- PSR-based transport: a PSR-18 client plus PSR-17 factories (auto-discovered via
  `php-http/discovery`), with no framework dependency. All HTTP I/O flows through a
  single `HttpTransport` chokepoint.
- Typed response DTOs for companies, checkouts, payments, refunds, voids, account
  links and webhooks; other endpoints return the decoded response array.
- A granular exception hierarchy under the `WhopException` base — `WhopApiException`
  (HTTP 4xx/5xx, carries status code and response body), `TransportException`,
  `SerializationException`, `MissingArgumentsException`, and
  `WebhookVerificationException` — so consumers can catch all SDK failures or a
  specific subtype.
- `WebhookVerifier` for Standard Webhooks signature verification, supporting both
  `whsec_` (production) and `ws_` (sandbox) secret formats, with raw-input and PSR-7
  request overloads.
- Full quality toolchain: PHPStan at `level: max`, PHP-CS-Fixer, Infection mutation
  testing, a comprehensive PHPUnit suite, and a GitHub Actions CI workflow.
