# Contributing to whop-php-sdk

Thanks for taking the time to contribute. This document covers the basics of working on the SDK.

## Reporting issues

Please use [GitHub Issues](https://github.com/devmatchable/whop-php-sdk/issues/new/choose) and pick the appropriate template. Include the PHP version, the PSR-18 HTTP client you're using, and a minimal reproduction wherever possible.

For security-sensitive reports, see [SECURITY.md](SECURITY.md) — please do not file public issues for vulnerabilities.

## Suggesting features

Open a feature request issue describing the use case and what you'd expect the API to look like. The SDK is intentionally framework-agnostic and built on PSR interfaces (PSR-7 / PSR-17 / PSR-18) — features that pull in framework-specific code should usually be proposed in the downstream [whop-symfony-bundle](https://github.com/devmatchable/whop-symfony-bundle) (or an equivalent framework adapter) instead of here.

## Local development

```bash
git clone https://github.com/devmatchable/whop-php-sdk.git
cd whop-php-sdk
composer install
```

Requires PHP 8.4+ and Composer 2.

## Quality gates

The same checks CI runs:

```bash
composer cs         # PHP-CS-Fixer (dry run + diff)
composer cs:fix     # Auto-fix style violations
composer stan       # PHPStan at level max
composer test       # PHPUnit
composer infection  # Mutation testing (xdebug required for coverage)
```

CI runs the full set across PHP 8.4 and PHP 8.5. All must pass before a PR can be merged.

## Pull request workflow

1. Fork the repo and create a branch from `master`.
2. Make your change — keep PRs focused on a single logical change.
3. Add or update tests covering the change.
4. Run the quality gates above locally.
5. Open a PR using the [pull request template](.github/PULL_REQUEST_TEMPLATE.md).
6. CI will run automatically. Address any reviewer feedback in follow-up commits.

## Commit messages

Conventional commit prefixes (`feat:`, `fix:`, `docs:`, `chore:`, `ci:`, `refactor:`, `test:`) are encouraged. Keep the subject line under 72 characters and explain the *why* in the body when the change is non-obvious.

## Code of Conduct

By participating in this project, you agree to abide by the project's [Code of Conduct](CODE_OF_CONDUCT.md).
