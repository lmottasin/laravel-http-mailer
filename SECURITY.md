# Security Policy

## Supported versions

| Version | Supported |
|---------|-----------|
| 1.x     | Yes       |

## Reporting a vulnerability

Please **do not** open a public issue for security problems.

Report it privately through [GitHub Security Advisories](https://github.com/lmottasin/laravel-http-mailer/security/advisories/new), or email **mottasin@workslayr.com**.

You can expect a reply within a few days. Once the issue is confirmed, a fix will be released as soon as possible and you will be credited (unless you'd rather not be).

## Protecting your API keys

- Keep `SENDGRID_API_KEY` and `BREVO_API_KEY` in `.env`, never in committed config.
- Give each key only the "Mail Send" permission it needs.
- Rotate a key straight away if it leaks.
