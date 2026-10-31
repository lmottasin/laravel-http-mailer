<?php

declare(strict_types=1);

use Lmottasin\HttpMailer\Drivers\BrevoDriver;
use Lmottasin\HttpMailer\Drivers\SendGridDriver;

return [

    /*
    |--------------------------------------------------------------------------
    | Mail transports
    |--------------------------------------------------------------------------
    |
    | Each class becomes a Laravel mail transport you can use as MAIL_MAILER.
    | To add another provider, create a class implementing
    | Lmottasin\HttpMailer\Contracts\TransportFactory and list it here.
    |
    | API keys live in config/services.php (services.sendgrid.key, ...).
    |
    */

    'drivers' => [
        SendGridDriver::class,
        BrevoDriver::class,
    ],

];
