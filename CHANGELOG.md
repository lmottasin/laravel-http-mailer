# Changelog

All notable changes to this package are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses [Semantic Versioning](https://semver.org/).

## [Unreleased]

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

[Unreleased]: https://github.com/lmottasin/laravel-http-mailer/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/lmottasin/laravel-http-mailer/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/lmottasin/laravel-http-mailer/releases/tag/v1.0.0
