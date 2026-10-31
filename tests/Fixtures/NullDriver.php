<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Tests\Fixtures;

use Lmottasin\HttpMailer\Contracts\TransportFactory;
use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * A custom provider registered through the `http-mailer.drivers` config.
 */
final class NullDriver implements TransportFactory
{
    public static function name(): string
    {
        return 'null-api';
    }

    /**
     * @param  array<array-key, mixed>  $config
     */
    public function transport(array $config): TransportInterface
    {
        return new NullTransport;
    }
}
