<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Drivers;

use Lmottasin\HttpMailer\Contracts\MailerDriver;
use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

class SendGridDriver implements MailerDriver
{
    public const NAME = 'sendgrid';

    public function name(): string
    {
        return self::NAME;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function transport(array $config): TransportInterface
    {
        $key = (string) ($config['key'] ?? config('http-mailer.sendgrid.key'));

        return (new SendgridTransportFactory)->create(
            new Dsn(scheme: 'sendgrid+api', host: 'default', user: $key),
        );
    }
}
