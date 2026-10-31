<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Drivers;

use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class SendGridDriver extends ApiDriver
{
    public static function name(): string
    {
        return 'sendgrid';
    }

    /**
     * @param  array<array-key, mixed>  $config
     */
    public function transport(array $config): TransportInterface
    {
        $region = $config['region'] ?? $this->config->get('services.sendgrid.region');

        return (new SendgridTransportFactory(client: $this->httpClient($config)))->create(new Dsn(
            scheme: 'sendgrid+api',
            host: 'default',
            user: $this->apiKey($config),
            options: is_string($region) && $region !== '' ? ['region' => $region] : [],
        ));
    }

    protected function apiKeyEnvironmentVariable(): string
    {
        return 'SENDGRID_API_KEY';
    }
}
