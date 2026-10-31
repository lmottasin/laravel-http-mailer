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

- <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/lock.svg" width="16" height="16" alt=""> **Problem:** cloud hosts block SMTP ports (25, 465, 587) for security, so Laravel emails time out.
- <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/arrow.svg" width="16" height="16" alt=""> **Fix:** send through the **SendGrid** or **Brevo** API over HTTPS (port 443).
- <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/code.svg" width="16" height="16" alt=""> **No changes to your email code.** Your Mailables and Notifications keep working as they are. Just one line in `.env`.

## Why

<picture>
  <source media="(max-width: 700px)" srcset="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/smtp-vs-api-mobile.png">
  <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/smtp-vs-api.png" alt="SMTP: ports 25, 465 and 587 are blocked on AWS, DigitalOcean, Google Cloud and Hetzner, and you see 'Connection timed out'. HTTP API: port 443 is open on every host, and errors are clear, like '403: sender not verified'. Your email code doesn't change." width="100%">
</picture>

## No code changes

Everything you already send keeps working. Only `.env` changes.

| | |
|---|---|
| <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/check.svg" width="16" height="16" alt=""> **Mailables** | `Mail::to($user)->send(new WelcomeMail)` |
| <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/check.svg" width="16" height="16" alt=""> **Notifications** (mail channel) | `$user->notify(new OrderShipped($order))` |
| <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/check.svg" width="16" height="16" alt=""> **Queued mail** | `Mail::to($user)->queue(new InvoiceMail)` |
| <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/check.svg" width="16" height="16" alt=""> **Raw mail** | `Mail::raw('Hi', fn ($m) => $m->to($email))` |

## How it compares

<picture>
  <source media="(max-width: 700px)" srcset="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/compare-mobile.png">
  <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/compare.png" alt="Comparison of this package with the Symfony mailer bridges, s-ichikawa/laravel-sendgrid-driver and the official sendgrid/sendgrid SDK." width="100%">
</picture>

<details>
<summary>Same comparison as text</summary>

| Package | Mailables & Notifications | SendGrid + Brevo | No glue code |
|---|:-:|:-:|:-:|
| **lmottasin/laravel-http-mailer** | <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/check.svg" width="16" height="16" alt="Yes"> | <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/check.svg" width="16" height="16" alt="Yes"> | <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/check.svg" width="16" height="16" alt="Yes"> |
| `symfony/*-mailer` bridges | <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/check.svg" width="16" height="16" alt="Yes"> | <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/cross.svg" width="16" height="16" alt="No"> | <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/cross.svg" width="16" height="16" alt="No"> |
| `s-ichikawa/laravel-sendgrid-driver` | <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/check.svg" width="16" height="16" alt="Yes"> | <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/cross.svg" width="16" height="16" alt="No"> | <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/check.svg" width="16" height="16" alt="Yes"> |
| `sendgrid/sendgrid` (SDK) | <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/cross.svg" width="16" height="16" alt="No"> | <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/cross.svg" width="16" height="16" alt="No"> | <img src="https://raw.githubusercontent.com/lmottasin/laravel-http-mailer/main/art/ui/cross.svg" width="16" height="16" alt="No"> |

</details>

## Requirements

| | Version |
|---|---|
| PHP | 8.2 or newer |
| Laravel | 12 or 13 (11 best-effort: it is end-of-life) |
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

**API keys** are read from `config/services.php`, like Laravel's own Mailgun, Postmark and Resend keys. The package fills in these defaults, so you only need to touch `services.php` to change them:

```php
'sendgrid' => [
    'key' => env('SENDGRID_API_KEY'),
    'region' => env('SENDGRID_REGION'), // "eu" for SendGrid EU accounts
],

'brevo' => [
    'key' => env('BREVO_API_KEY'),
],
```

> [!NOTE]
> If your `config/services.php` already has a `sendgrid` or `brevo` block, the package leaves it as it is. Add `'region' => env('SENDGRID_REGION')` to it yourself if you need the EU region.

**Per-mailer settings.** The package adds `sendgrid` and `brevo` to `mail.mailers` for you. If you define them yourself in `config/mail.php`, yours wins:

```php
'mailers' => [
    'sendgrid' => [
        'transport' => 'sendgrid',
        'key' => env('MY_OTHER_SENDGRID_KEY'), // if this is missing you get a clear error, never another account's key
        'region' => 'eu',                      // SendGrid EU data residency ("global" or "eu")
        'client' => ['timeout' => 5],          // Symfony HTTP client options
    ],
],
```

**A missing or empty key** throws `MissingApiKeyException` with the name of the `.env` variable to set, instead of failing later with an HTTP 401.

**Safe HTTP defaults.** Every request has a 10 s timeout, a 30 s maximum duration and does not follow redirects, so a slow API can't hang your app and the API key is never sent to another host. Change them with the mailer's `client` option.

**Region** only accepts `global` or `eu`. Anything else throws, because the value becomes part of the API hostname.

## Supported providers

| Provider | `MAIL_MAILER` value | API key env | Built on |
|---|---|---|---|
| <img src="https://cdn.simpleicons.org/sendgrid/1A82E2" width="16"> SendGrid | `sendgrid` | `SENDGRID_API_KEY` | `symfony/sendgrid-mailer` |
| <img src="https://cdn.simpleicons.org/brevo/0B996E" width="16"> Brevo (formerly Sendinblue) | `brevo` | `BREVO_API_KEY` | `symfony/brevo-mailer` |

Need Mailgun, Postmark, Resend or something else? Check whether Laravel already supports it (Mailgun, Postmark, Resend and SES are built in). If not, you can add it yourself in about 20 lines. See below.

## Adding a new provider

You can add a provider from your own app, without forking the package.

**1. Create a class** that implements `TransportFactory` (for example `app/Mail/FooTransportFactory.php`):

```php
use Lmottasin\HttpMailer\Contracts\TransportFactory;
use Symfony\Component\Mailer\Bridge\Foo\Transport\FooTransportFactory as SymfonyFoo;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class FooTransportFactory implements TransportFactory
{
    public static function name(): string
    {
        return 'foo'; // MAIL_MAILER=foo
    }

    public function transport(array $config): TransportInterface
    {
        return (new SymfonyFoo)->create(
            new Dsn('foo+api', 'default', $config['key'] ?? config('services.foo.key')),
        );
    }
}
```

**2. List it** in the published config (`php artisan vendor:publish --tag=http-mailer-config`):

```php
// config/http-mailer.php
'drivers' => [
    SendGridDriver::class,
    BrevoDriver::class,
    App\Mail\FooTransportFactory::class,
],
```

That's it: `MAIL_MAILER=foo` now works. Names must be unique, can't contain a dot, and can't reuse a Laravel transport name such as `smtp` or `log`. Want it built in for everyone? See [CONTRIBUTING.md](CONTRIBUTING.md).

## How it works under the hood

1. **`register()`** merges the package config and default `services` keys, then adds a `mail.mailers.<name>` entry for each driver (unless you already defined one).
2. **`boot()`** waits until Laravel's mail manager is first used, then registers each driver with `extend()`. Requests that send no mail pay nothing.
3. When Laravel sends an email, the driver builds a Symfony transport through the official SendGrid or Brevo bridge, and that transport makes an HTTPS request to the provider's API.

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
| `MissingApiKeyException` | Set the `.env` variable named in the message, then `php artisan config:clear` |
| Timeout on port 587 | Set `MAIL_MAILER=sendgrid` (or `brevo`) |
| `.env` changes ignored | `php artisan config:clear` |
| SendGrid EU account rejects requests | Set `SENDGRID_REGION=eu` |
| `Unsupported mail transport [sendgrid]` | `php artisan optimize:clear` (stale package or config cache) |
| Queued mail lost | Check `failed_jobs` and `storage/logs` |

</details>

## FAQ

**Why do Laravel emails time out on DigitalOcean, AWS, Google Cloud or Hetzner?**
These hosts block outgoing SMTP ports (25, 465, 587) to stop spam. Laravel's default `smtp` mailer uses those ports, so the connection times out.

**How do I send Laravel email when port 587 is blocked?**
Install this package and set `MAIL_MAILER=sendgrid` (or `brevo`). Mail goes out over the provider's HTTPS API on port 443, which is never blocked.

**Do I need to change my Mailables or Notifications?**
No. `Mail::to()->send()`, queued mail and mail Notifications keep working. Only `.env` changes.

**Can I just ask my host to unblock SMTP?**
Sometimes, but it takes a support ticket and is often refused. An HTTP API works right away.

**SendGrid or Brevo?**
Either. Set both keys and use Laravel's `failover` mailer to switch automatically if one is down.

**Does it work with Laravel 11, 12 and 13, Octane and queues?**
Yes. Laravel 12 and 13 are fully supported, 11 is best-effort (end-of-life). Octane, queue workers and `config:cache` are covered by the tests.

## Related links

**Why your host blocks SMTP**
- [DigitalOcean: Why is SMTP blocked?](https://docs.digitalocean.com/support/why-is-smtp-blocked/): ports 25, 465 and 587 are blocked on all Droplets by default.
- [AWS: Remove the port 25 restriction on EC2](https://repost.aws/knowledge-center/ec2-port-25-throttle): outbound port 25 is throttled until AWS approves a request.
- [Google Cloud: Sending email from an instance](https://cloud.google.com/compute/docs/tutorials/sending-mail): port 25 is blocked for destinations outside your VPC.
- [Hetzner Cloud FAQ: Why can I not send any mails from my server?](https://docs.hetzner.com/cloud/servers/faq/#why-can-i-not-send-any-mails-from-my-server): ports 25 and 465 are blocked on new cloud servers.

**Developers hitting the same timeout**
- [DigitalOcean Community: SMTP port 587 is closed](https://www.digitalocean.com/community/questions/smtp-587-ports-is-closed)
- [DigitalOcean Community: telnet to port 587 times out](https://www.digitalocean.com/community/questions/telnet-to-port-587-timeout)

**The APIs and Laravel docs this package builds on**
- [SendGrid v3 Mail Send API](https://www.twilio.com/docs/sendgrid/api-reference/mail-send)
- [Brevo transactional email API](https://developers.brevo.com/reference/sendtransacemail)
- [Laravel Mail documentation](https://laravel.com/docs/mail)

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
