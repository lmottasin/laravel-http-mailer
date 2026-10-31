<picture>
  <source media="(max-width: 700px)" srcset="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/hero-mobile.png">
  <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/hero.svg" alt="Cloud hosts block SMTP ports for security. Send over HTTPS port 443 instead. Your email code doesn't change." width="100%">
</picture>

<p align="center">
  <a href="https://github.com/lmottasin/laravel-http-mailer/actions/workflows/tests.yml"><img src="https://github.com/lmottasin/laravel-http-mailer/actions/workflows/tests.yml/badge.svg" alt="Tests"></a>
  <a href="https://packagist.org/packages/lmottasin/laravel-http-mailer"><img src="https://img.shields.io/packagist/v/lmottasin/laravel-http-mailer.svg" alt="Latest Version"></a>
  <a href="https://packagist.org/packages/lmottasin/laravel-http-mailer"><img src="https://img.shields.io/packagist/dt/lmottasin/laravel-http-mailer.svg" alt="Total Downloads"></a>
  <a href="LICENSE.md"><img src="https://img.shields.io/packagist/l/lmottasin/laravel-http-mailer.svg" alt="License"></a>
</p>

# Laravel HTTP Mailer

- 🔒 **Problem:** cloud hosts block SMTP ports (25, 465, 587) for security, so Laravel emails time out.
- ✅ **Fix:** send through the **SendGrid** or **Brevo** API over HTTPS (port 443).
- ✨ **No changes to your email code.** Mailables, Notifications and `Mail::` keep working. Just one line in `.env`.

## Why

<picture>
  <source media="(max-width: 700px)" srcset="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/smtp-vs-api-mobile.png">
  <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/smtp-vs-api.png" alt="SMTP: ports 25, 465 and 587 are blocked on AWS, DigitalOcean, Google Cloud and Hetzner, and you see 'Connection timed out'. HTTP API: port 443 is open on every host, and errors are clear, like '403: sender not verified'. Your email code doesn't change." width="100%">
</picture>

## Requirements

| | Version |
|---|---|
| PHP | 8.2 or newer |
| Laravel | 11, 12 or 13 |
| Account | SendGrid **or** Brevo, with an API key |

## Setup in 3 steps

### 1. Install

```bash
composer require lmottasin/laravel-http-mailer
```

Laravel finds the package automatically. You don't have to register anything.

### 2. Update your `.env`

A fresh Laravel app has a mail block like the one below. **Comment out the SMTP lines you don't need anymore**, then add your provider settings.

<details open>
<summary><b>Using SendGrid</b></summary>

```dotenv
# --- Old SMTP settings (not needed anymore, keep them commented) ---
# MAIL_MAILER=smtp
# MAIL_SCHEME=null
# MAIL_HOST=smtp.sendgrid.net
# MAIL_PORT=587
# MAIL_USERNAME=apikey
# MAIL_PASSWORD=SG.xxxxxxxx

# --- New: send through the SendGrid HTTP API ---
MAIL_MAILER=sendgrid
SENDGRID_API_KEY=SG.xxxxxxxxxxxxxxxxxxxxxxxx

# --- Keep these, they are still used ---
MAIL_FROM_ADDRESS="no-reply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Get a key at **SendGrid → Settings → API Keys → Create API Key** (the "Mail Send" permission is enough).

</details>

<details>
<summary><b>Using Brevo</b></summary>

```dotenv
# --- Old SMTP settings (not needed anymore, keep them commented) ---
# MAIL_MAILER=smtp
# MAIL_SCHEME=null
# MAIL_HOST=smtp-relay.brevo.com
# MAIL_PORT=587
# MAIL_USERNAME=your-login@smtp-brevo.com
# MAIL_PASSWORD=xsmtpsib-xxxxxxxx

# --- New: send through the Brevo HTTP API ---
MAIL_MAILER=brevo
BREVO_API_KEY=xkeysib-xxxxxxxxxxxxxxxxxxxxxxxx

# --- Keep these, they are still used ---
MAIL_FROM_ADDRESS="no-reply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Get a key at **Brevo → SMTP & API → API Keys → Generate a new API key**.
Note: Brevo's **API key** starts with `xkeysib-`. The SMTP password (`xsmtpsib-`) won't work here.

</details>

<details>
<summary><b>Want both? Set both keys and switch anytime</b></summary>

```dotenv
MAIL_MAILER=sendgrid            # change to "brevo" to switch
SENDGRID_API_KEY=SG.xxxxxxxx
BREVO_API_KEY=xkeysib-xxxxxxxx
```

</details>

| Variable | What it does | Required? |
|---|---|---|
| `MAIL_MAILER` | Which provider to use: `sendgrid` or `brevo` | Yes |
| `SENDGRID_API_KEY` | Your SendGrid API key (starts with `SG.`) | If using SendGrid |
| `BREVO_API_KEY` | Your Brevo API key (starts with `xkeysib-`) | If using Brevo |
| `MAIL_FROM_ADDRESS` | Sender email. **Its domain must be verified with your provider.** | Yes |
| `MAIL_FROM_NAME` | Sender name shown in the inbox | Recommended |
| `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME` | SMTP only. Not used by this package. | No |

### 3. Clear the config cache

```bash
php artisan config:clear
```

That's it. Send a test email:

```bash
php artisan tinker --execute 'Mail::raw("It works!", fn ($m) => $m->to("you@example.com")->subject("Test"));'
```

> [!IMPORTANT]
> **Verify your sender domain first.** The domain in `MAIL_FROM_ADDRESS` has to be verified in your provider's dashboard
> (SendGrid → *Sender Authentication*, Brevo → *Senders, Domains & Dedicated IPs*).
> If it isn't, the API rejects the email with **HTTP 403**.

## Usage (no code changes)

Nothing to change. Keep writing mail code exactly as you do today:

```php
Mail::to($user)->send(new WelcomeMail($user));

$user->notify(new OrderShipped($order));

Mail::raw('Hello!', fn ($message) => $message->to('jane@example.com')->subject('Hi'));
```

Send one email through a different provider than your default:

```php
Mail::mailer('brevo')->to($user)->send(new InvoiceMail($invoice));
```

### Automatic backup provider (failover)

If SendGrid has an outage, Laravel can retry the email through Brevo automatically. Add this to `config/mail.php`:

```php
'mailers' => [
    // ...your other mailers

    'api' => [
        'transport' => 'failover',
        'mailers' => ['sendgrid', 'brevo'], // tried in this order
    ],
],
```

Then set `MAIL_MAILER=api` in `.env`, along with both API keys.

## Optional settings

**Publish the config file**, if you want to see or change where the keys come from:

```bash
php artisan vendor:publish --tag=http-mailer-config
```

This creates `config/http-mailer.php`:

```php
return [
    'sendgrid' => ['key' => env('SENDGRID_API_KEY')],
    'brevo'    => ['key' => env('BREVO_API_KEY')],
];
```

**Define a mailer yourself.** The package adds `sendgrid` and `brevo` to `mail.mailers` for you. If you add your own entry in `config/mail.php`, yours wins:

```php
'mailers' => [
    'sendgrid' => [
        'transport' => 'sendgrid',
        'key' => env('MY_OTHER_SENDGRID_KEY'),
    ],
],
```

## Supported providers

| Provider | `MAIL_MAILER` value | API key env | Built on |
|---|---|---|---|
| <img src="https://cdn.simpleicons.org/sendgrid/1A82E2" width="16"> SendGrid | `sendgrid` | `SENDGRID_API_KEY` | `symfony/sendgrid-mailer` |
| <img src="https://cdn.simpleicons.org/brevo/0B996E" width="16"> Brevo (formerly Sendinblue) | `brevo` | `BREVO_API_KEY` | `symfony/brevo-mailer` |

Need Mailgun, Postmark, Resend or something else? Check whether Laravel already supports it (Mailgun, Postmark, Resend and SES are built in). If not, adding it here takes about 20 lines. See below.

## Adding a new provider

Each provider is one small class. Say you want to add **Foo**:

**1. Create the driver** in `src/Drivers/FooDriver.php`:

```php
<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Drivers;

use Lmottasin\HttpMailer\Contracts\MailerDriver;
use Symfony\Component\Mailer\Bridge\Foo\Transport\FooTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

class FooDriver implements MailerDriver
{
    public const NAME = 'foo';            // becomes MAIL_MAILER=foo

    public function name(): string
    {
        return self::NAME;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function transport(array $config): TransportInterface
    {
        $key = (string) ($config['key'] ?? config('http-mailer.foo.key'));

        return (new FooTransportFactory)->create(
            new Dsn(scheme: 'foo+api', host: 'default', user: $key),
        );
    }
}
```

**2. Register it** in `src/HttpMailerServiceProvider.php`:

```php
protected array $drivers = [
    SendGridDriver::class,
    BrevoDriver::class,
    FooDriver::class,   // 👈 add this
];
```

**3. Add its key** to `config/http-mailer.php`:

```php
'foo' => ['key' => env('FOO_API_KEY')],
```

**4. Add the Symfony bridge** (if one exists) to `require` in `composer.json`, e.g. `symfony/foo-mailer`.

**5. Add tests** in `tests/Unit/FooDriverTest.php` and a case in `tests/Feature/HttpMailerServiceProviderTest.php`, then run `composer test`.

Apps can then use `MAIL_MAILER=foo` and `FOO_API_KEY=...`. PRs for new providers are very welcome. See [CONTRIBUTING.md](CONTRIBUTING.md).

## How it works under the hood

1. **`register()`** loads the package config, registers each driver, and adds a `mail.mailers.<name>` entry (unless you already defined one).
2. **`boot()`** calls Laravel's `Mail::extend()` for each driver.
3. When Laravel sends an email, the driver builds a Symfony transport through the official SendGrid or Brevo bridge, and that transport makes an HTTPS request to the provider's API.

## How it compares

<picture>
  <source media="(max-width: 700px)" srcset="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/compare-mobile.png">
  <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/compare.png" alt="Comparison of this package with the Symfony mailer bridges, s-ichikawa/laravel-sendgrid-driver and the official sendgrid/sendgrid SDK." width="100%">
</picture>

<details>
<summary>Same comparison as text</summary>

| Package | Works with `Mail::` | SendGrid + Brevo | No glue code |
|---|:-:|:-:|:-:|
| **lmottasin/laravel-http-mailer** | ✅ | ✅ | ✅ |
| `symfony/*-mailer` bridges | ❌ | ❌ | ❌ |
| `s-ichikawa/laravel-sendgrid-driver` | ✅ | ❌ | ✅ |
| `sendgrid/sendgrid` (SDK) | ❌ | ❌ | ❌ |

</details>

## Troubleshooting

<picture>
  <source media="(max-width: 700px)" srcset="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/troubleshooting-mobile.png">
  <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/troubleshooting.png" alt="Troubleshooting: common errors and their fixes." width="100%">
</picture>

<details>
<summary>Same fixes as text</summary>

| Error | Fix |
|---|---|
| `403` sender not verified | Verify your domain with the provider |
| `401` invalid API key | Check the key. Brevo needs `xkeysib-`, not the SMTP password |
| Timeout on port 587 | Set `MAIL_MAILER=sendgrid` (or `brevo`) |
| `.env` changes ignored | `php artisan config:clear` |
| `Unsupported mail transport [sendgrid]` | `composer dump-autoload` |
| Queued mail lost | Check `failed_jobs` and `storage/logs` |

</details>

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

Found a security problem? Please report it privately. See [SECURITY.md](SECURITY.md).

## Credits

- [lmottasin](https://github.com/lmottasin) · lmottasin@gmail.com
- [All contributors](https://github.com/lmottasin/laravel-http-mailer/contributors)
- Provider logos from [Simple Icons](https://simpleicons.org) (CC0). SendGrid, Brevo and Laravel are trademarks of their owners. This package is not affiliated with them.

## License

MIT. See [LICENSE.md](LICENSE.md).
