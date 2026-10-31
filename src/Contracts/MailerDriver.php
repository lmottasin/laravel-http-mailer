<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Contracts;

use Symfony\Component\Mailer\Transport\TransportInterface;

interface MailerDriver
{
    /**
     * Mail driver key (e.g. 'sendgrid', 'brevo'). MUST equal the implementing
     * class's `NAME` constant so the ServiceProvider can register the driver
     * without instantiating it.
     */
    public function name(): string;

    /**
     * Build a Symfony Mailer transport from the Laravel `config/mail.php`
     * mailer config array (typically containing the API key).
     *
     * @param  array<string, mixed>  $config
     */
    public function transport(array $config): TransportInterface;
}
