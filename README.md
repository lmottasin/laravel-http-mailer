# Laravel HTTP Mailer

[![Tests](https://github.com/lmottasin/laravel-http-mailer/actions/workflows/tests.yml/badge.svg)](https://github.com/lmottasin/laravel-http-mailer/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/lmottasin/laravel-http-mailer.svg)](https://packagist.org/packages/lmottasin/laravel-http-mailer)
[![Total Downloads](https://img.shields.io/packagist/dt/lmottasin/laravel-http-mailer.svg)](https://packagist.org/packages/lmottasin/laravel-http-mailer)
[![License](https://img.shields.io/packagist/l/lmottasin/laravel-http-mailer.svg)](LICENSE.md)

Send Laravel mail through the **SendGrid** and **Brevo** HTTP APIs instead of SMTP.

Install it, set one env variable, and every `Mail::send()`, Mailable and mail Notification in your app goes out over HTTPS. No code changes.

## Why?

Many cloud hosts (DigitalOcean, AWS, GCP, Hetzner and others) block outbound SMTP ports **25, 465 and 587** by default. Laravel's `smtp` mailer then can't reach `smtp.sendgrid.net` or `smtp-relay.brevo.com`, and your emails time out quietly in production.

SendGrid and Brevo both have REST APIs on **port 443**, which is never blocked. This package registers `sendgrid` and `brevo` mail drivers that talk to those APIs, using Symfony's official mailer bridges under the hood.

## Requirements

| Package | Version |
|---------|---------|
| PHP     | 8.2+    |
| Laravel | 11, 12, 13 |

## Installation

```bash
composer require lmottasin/laravel-http-mailer
```

The service provider is auto-discovered. You don't need to edit `config/mail.php` or `config/services.php`.

## Configuration

Add your API key and pick the mailer in `.env`:

```dotenv
# SendGrid
MAIL_MAILER=sendgrid
SENDGRID_API_KEY=SG.xxxxxxxx

# ...or Brevo
MAIL_MAILER=brevo
BREVO_API_KEY=xkeysib-xxxxxxxx

MAIL_FROM_ADDRESS=no-reply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

Then clear the config cache:

```bash
php artisan config:clear
```

> [!IMPORTANT]
> The `MAIL_FROM_ADDRESS` domain must be verified in your provider's dashboard
> (SendGrid → *Sender Authentication*, Brevo → *Senders, Domains & Dedicated IPs*).
> If it isn't, the API rejects the email with HTTP 403.

### Publishing the config (optional)

```bash
php artisan vendor:publish --tag=http-mailer-config
```

This copies `config/http-mailer.php` into your app:

```php
return [
    'sendgrid' => ['key' => env('SENDGRID_API_KEY')],
    'brevo'    => ['key' => env('BREVO_API_KEY')],
];
```

### Custom mailer entries (optional)

The package adds `mail.mailers.sendgrid` and `mail.mailers.brevo` for you. If you define them yourself in `config/mail.php`, your version wins. You can also pass the key there directly:

```php
'mailers' => [
    'sendgrid' => [
        'transport' => 'sendgrid',
        'key' => env('SENDGRID_API_KEY'),
    ],
],
```

## Usage

Nothing changes in your code:

```php
Mail::to($user)->send(new WelcomeMail($user));

$user->notify(new OrderShipped($order));

Mail::raw('Hello!', fn ($message) => $message->to('jane@example.com')->subject('Hi'));
```

Send one message through a different provider than your default:

```php
Mail::mailer('brevo')->to($user)->send(new InvoiceMail($invoice));
```

### Failover

Combine both providers with Laravel's built-in `failover` mailer, so Brevo takes over if SendGrid is down:

```php
// config/mail.php
'mailers' => [
    'api' => [
        'transport' => 'failover',
        'mailers' => ['sendgrid', 'brevo'],
    ],
],
```

```dotenv
MAIL_MAILER=api
```

## Supported providers

| Driver     | Provider               | Uses                       |
|------------|------------------------|----------------------------|
| `sendgrid` | SendGrid               | `symfony/sendgrid-mailer`  |
| `brevo`    | Brevo (ex-Sendinblue)  | `symfony/brevo-mailer`     |

Want another provider? See [Adding a provider](#adding-a-provider).

## How it compares

| Package | Providers | Works with `Mail::` out of the box | Multiple providers |
|---------|-----------|-----------------------------------|--------------------|
| **lmottasin/laravel-http-mailer** | SendGrid, Brevo (extensible) | Yes | Yes |
| `symfony/sendgrid-mailer` / `symfony/brevo-mailer` | One each | No, needs `Mail::extend()` glue | No |
| `s-ichikawa/laravel-sendgrid-driver` | SendGrid | Yes | No |
| `sendgrid/sendgrid` (official SDK) | SendGrid | No, not a mail driver | No |

## How it works

- `register()` merges the package config, binds each driver as a singleton, and adds a `mail.mailers.<name>` entry unless you already have one.
- `boot()` calls `Mail::extend()` for each driver. When Laravel needs the transport, the driver builds it through the official Symfony bridge.

## Adding a provider

1. Create `src/Drivers/FooDriver.php` that implements `Lmottasin\HttpMailer\Contracts\MailerDriver`, with `public const NAME = 'foo';` and a `transport()` method returning a Symfony `TransportInterface`.
2. Add `FooDriver::class` to the `$drivers` array in `HttpMailerServiceProvider`.
3. Add the Symfony bridge (if any) to `require` in `composer.json`.
4. Add `'foo' => ['key' => env('FOO_API_KEY')]` to `config/http-mailer.php`.
5. Add tests in `tests/Unit` and `tests/Feature`.

Pull requests for new providers are welcome. See [CONTRIBUTING.md](CONTRIBUTING.md).

## Troubleshooting

| Problem | Fix |
|---------|-----|
| HTTP 403 from the API | Verify your sender domain in the provider dashboard. |
| `Unsupported mail transport [sendgrid]` | Run `composer dump-autoload` and `php artisan config:clear`. Check the package is in `vendor/`. |
| Queued mail never arrives | Look in `failed_jobs` and your log for a `Symfony\Component\Mailer\Exception\TransportException`. It contains the provider's error body. |
| Wrong API key is used | Run `php artisan config:clear`. A cached config ignores new `.env` values. |

## Testing

```bash
composer install
composer test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## Security

Please report vulnerabilities privately. See [SECURITY.md](SECURITY.md).

## Credits

- [lmottasin](https://github.com/lmottasin)
- [All contributors](https://github.com/lmottasin/laravel-http-mailer/contributors)

## License

MIT. See [LICENSE.md](LICENSE.md).
