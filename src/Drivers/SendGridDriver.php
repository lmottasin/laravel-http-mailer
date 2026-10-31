<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Drivers;

use InvalidArgumentException;
use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class SendGridDriver extends ApiDriver
{
    /**
     * SendGrid API regions. The value becomes part of the API hostname, so only known regions are allowed.
     */
    public const REGIONS = ['global', 'eu'];

    public static function name(): string
    {
        return 'sendgrid';
    }

    /**
     * @param  array<array-key, mixed>  $config
     */
    public function transport(#[\SensitiveParameter] array $config): TransportInterface
    {
        $region = $this->region($config);

        return (new SendgridTransportFactory(client: $this->httpClient($config)))->create(new Dsn(
            scheme: 'sendgrid+api',
            host: 'default',
            user: $this->apiKey($config),
            options: $region === null ? [] : ['region' => $region],
        ));
    }

    protected function apiKeyEnvironmentVariable(): string
    {
        return 'SENDGRID_API_KEY';
    }

    /**
     * The mailer's own `region` when it defines one, otherwise `services.sendgrid.region`.
     *
     * @param  array<array-key, mixed>  $config
     *
     * @throws InvalidArgumentException
     */
    private function region(#[\SensitiveParameter] array $config): ?string
    {
        $region = array_key_exists('region', $config)
            ? $config['region']
            : $this->config->get('services.sendgrid.region');

        if ($region === null || $region === '') {
            return null;
        }

        $normalized = is_string($region) ? strtolower(trim($region)) : null;

        if ($normalized === null || ! in_array($normalized, self::REGIONS, true)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported SendGrid region [%s]. Use one of: %s.',
                is_string($region) ? $region : get_debug_type($region),
                implode(', ', self::REGIONS),
            ));
        }

        return $normalized;
    }
}
