<p align="center">
  <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/hero.png" alt="Laravel HTTP Mailer: send mail over HTTPS with SendGrid and Brevo when SMTP is blocked" width="100%">
</p>

<p align="center">
  <a href="https://github.com/lmottasin/laravel-http-mailer/actions/workflows/tests.yml"><img src="https://github.com/lmottasin/laravel-http-mailer/actions/workflows/tests.yml/badge.svg" alt="Tests"></a>
  <a href="https://packagist.org/packages/lmottasin/laravel-http-mailer"><img src="https://img.shields.io/packagist/v/lmottasin/laravel-http-mailer.svg" alt="Latest Version"></a>
  <a href="https://packagist.org/packages/lmottasin/laravel-http-mailer"><img src="https://img.shields.io/packagist/dt/lmottasin/laravel-http-mailer.svg" alt="Total Downloads"></a>
  <a href="LICENSE.md"><img src="https://img.shields.io/packagist/l/lmottasin/laravel-http-mailer.svg" alt="License"></a>
</p>

# Laravel HTTP Mailer

**In short:** your server blocks SMTP, so Laravel can't send email. This package sends it through the **SendGrid** or **Brevo** HTTP API instead, over normal HTTPS. You install it, change one line in `.env`, and your existing emails start working again. You don't touch any PHP code.

## Why do I need this?

Most cloud hosts (DigitalOcean, AWS, Google Cloud, Hetzner, and others) **block outgoing SMTP ports 25, 465 and 587** to fight spam. Laravel's default `smtp` mailer uses those ports, so in production your emails just time out, often with no clear error.

SendGrid and Brevo both have an HTTP API on **port 443**, the same port every website uses, so it's never blocked. This package plugs those APIs into Laravel's mail system.

<p align="center">
  <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/how-it-works.png" alt="How it works: your Laravel code, this package's HTTP driver, then SendGrid or Brevo over HTTPS port 443, then the inbox" width="100%">
</p>

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

## Usage

Keep writing mail code exactly as you do today:

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

| Package | Providers | Works with `Mail::` out of the box | Multiple providers |
|---|---|---|---|
| **lmottasin/laravel-http-mailer** | SendGrid, Brevo (easy to extend) | ✅ | ✅ |
| `symfony/sendgrid-mailer`, `symfony/brevo-mailer` | One each | ❌ You write the `Mail::extend()` glue | ❌ |
| `s-ichikawa/laravel-sendgrid-driver` | SendGrid | ✅ | ❌ |
| `sendgrid/sendgrid` (official SDK) | SendGrid | ❌ Not a mail driver | ❌ |

## Troubleshooting

| You see | What to do |
|---|---|
| **HTTP 403** from the API | The sender domain isn't verified. Verify it in your provider's dashboard. |
| **401 / "Unauthorized"** | Wrong API key. For Brevo, use the `xkeysib-` API key, not the SMTP password. |
| `Unsupported mail transport [sendgrid]` | Run `composer dump-autoload` and `php artisan config:clear`. |
| New `.env` values are ignored | Your config is cached. Run `php artisan config:clear` (or `config:cache` again in production). |
| Queued emails never arrive | Check the `failed_jobs` table and `storage/logs`. Look for `TransportException`, which contains the provider's error message. |
| Still connecting to port 587 | `MAIL_MAILER` is still `smtp`. Set it to `sendgrid` or `brevo`. |

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
