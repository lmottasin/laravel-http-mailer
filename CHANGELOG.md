# Changelog

All notable changes to this package are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses [Semantic Versioning](https://semver.org/).

## [Unreleased]

## [2.0.0] - 2026-10-31

### Added
- Register your own providers from the app via `http-mailer.drivers`, no fork needed.
- `MissingApiKeyException`: a missing or empty API key fails fast with the `.env` variable to set, instead of an HTTP 401 at send time.
- SendGrid EU data residency: `SENDGRID_REGION=eu` or `'region' => 'eu'` on the mailer.
- HTTP client options per mailer (`'client' => ['timeout' => 10]`).
- Laravel 12/13 on PHP 8.5 in CI, plus lowest-dependency runs, Pint and PHPStan (level max).

### Changed
- **Breaking:** `Contracts\MailerDriver` is now `Contracts\TransportFactory` with a static `name()`. The `NAME` constants and instance `name()` methods are gone.
- **Breaking:** `SendGridDriver` and `BrevoDriver` are `final` and take the config repository (and an optional HTTP client) in their constructor.
- **Breaking:** API keys now live in `config/services.php` (`services.sendgrid.key`, `services.brevo.key`), like Laravel's built-in mailers. `config/http-mailer.php` only lists the drivers. The `.env` variable names are unchanged.
- A mailer that defines its own `key` no longer falls back to the default key when that value is missing.
- Transports are registered when the mail manager is first resolved, not on every boot.
- Requires `symfony/sendgrid-mailer` 7.2 or newer.
- Laravel 11 support is best-effort (it is end-of-life).

### Upgrading from 1.x
- Using only `.env` keys: nothing to do.
- Published `config/http-mailer.php`: move any `sendgrid.key` / `brevo.key` entries to `config/services.php`, then re-publish.
- Custom drivers: implement `TransportFactory` with `public static function name(): string` and list the class in `http-mailer.drivers`.

## [1.1.1] - 2026-10-31

### Changed
- Friendlier README with images, `.env` setup guide and provider guide.

## [1.1.0] - 2026-10-31

### Added
- Laravel 11 support (Laravel 11, 12 and 13 on PHP 8.2+).

## [1.0.0] - 2026-10-31

### Added
- `sendgrid` mail driver using the SendGrid v3 HTTP API.
- `brevo` mail driver using the Brevo v3 HTTP API.
- Auto-registration of `mail.mailers.sendgrid` and `mail.mailers.brevo`.
- Publishable `http-mailer` config (`--tag=http-mailer-config`).
- `MailerDriver` contract for adding new providers.
- Support for Laravel 12 and 13 on PHP 8.2+.

[Unreleased]: https://github.com/lmottasin/laravel-http-mailer/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/lmottasin/laravel-http-mailer/compare/v1.1.1...v2.0.0
[1.1.1]: https://github.com/lmottasin/laravel-http-mailer/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/lmottasin/laravel-http-mailer/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/lmottasin/laravel-http-mailer/releases/tag/v1.0.0
