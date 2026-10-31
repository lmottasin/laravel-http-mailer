<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Contracts;

use Symfony\Component\Mailer\Transport\TransportInterface;

interface TransportFactory
{
    /**
     * The Laravel mail transport name, used as `'transport' => '<name>'` in config/mail.php
     * and as `MAIL_MAILER=<name>` in .env.
     */
    public static function name(): string;

    /**
     * Build a Symfony Mailer transport from a Laravel mailer config array.
     *
     * @param  array<array-key, mixed>  $config
     */
    public function transport(#[\SensitiveParameter] array $config): TransportInterface;
}
