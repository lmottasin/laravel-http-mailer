# Contributing

Thanks for helping out! Contributions are welcome through pull requests on [GitHub](https://github.com/lmottasin/laravel-http-mailer).

## Getting started

```bash
git clone https://github.com/<your-username>/laravel-http-mailer.git
cd laravel-http-mailer
composer install
composer test
```

## Pull requests

- **One change per PR.** Send separate PRs for unrelated changes.
- **Add tests.** A PR without tests for new behaviour will probably not be merged.
- **Update the docs.** Change `README.md` if behaviour changes, and add a line under `[Unreleased]` in `CHANGELOG.md`.
- **Follow [SemVer](https://semver.org/).** Don't break the public API without a good reason.
- **Keep commits clean.** Squash "fix typo" commits before asking for review.

## Adding a new provider

1. Create `src/Drivers/<Provider>Driver.php` implementing `Lmottasin\HttpMailer\Contracts\MailerDriver`.
2. Register it in the `$drivers` array of `HttpMailerServiceProvider`.
3. Add the Symfony bridge to `composer.json` (if one exists).
4. Add its key to `config/http-mailer.php`.
5. Add a unit test in `tests/Unit` and extend `tests/Feature/HttpMailerServiceProviderTest.php`.
6. Add the provider to the "Supported providers" table in `README.md`.

## Reporting bugs

Open an [issue](https://github.com/lmottasin/laravel-http-mailer/issues) with your PHP and Laravel versions, the provider, and the full error message.

Security problems should **not** go in public issues. See [SECURITY.md](SECURITY.md).
