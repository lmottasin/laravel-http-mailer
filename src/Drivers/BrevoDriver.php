<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Drivers;

use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class BrevoDriver extends ApiDriver
{
    public static function name(): string
    {
        return 'brevo';
    }

    /**
     * @param  array<array-key, mixed>  $config
     */
    public function transport(#[\SensitiveParameter] array $config): TransportInterface
    {
        return (new BrevoTransportFactory(client: $this->httpClient($config)))->create(new Dsn(
            scheme: 'brevo+api',
            host: 'default',
            user: $this->apiKey($config),
        ));
    }

    protected function apiKeyEnvironmentVariable(): string
    {
        return 'BREVO_API_KEY';
    }
}
