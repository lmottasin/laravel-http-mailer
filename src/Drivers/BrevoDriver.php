<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Drivers;

use Lmottasin\HttpMailer\Contracts\MailerDriver;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

class BrevoDriver implements MailerDriver
{
    public const NAME = 'brevo';

    public function name(): string
    {
        return self::NAME;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function transport(array $config): TransportInterface
    {
        $key = (string) ($config['key'] ?? config('http-mailer.brevo.key'));

        return (new BrevoTransportFactory)->create(
            new Dsn(scheme: 'brevo+api', host: 'default', user: $key),
        );
    }
}
