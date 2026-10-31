<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Drivers;

use Illuminate\Contracts\Config\Repository;
use InvalidArgumentException;
use Lmottasin\HttpMailer\Contracts\TransportFactory;
use Lmottasin\HttpMailer\Exceptions\MissingApiKeyException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Shared plumbing for HTTP API transports: API key lookup and the HTTP client.
 */
abstract class ApiDriver implements TransportFactory
{
    /**
     * HTTP client defaults. A mailer's `client` options override them.
     *
     * - timeout / max_duration: a slow API can't hang a request that sends mail synchronously.
     * - max_redirects: the APIs never redirect, and a redirect could forward the API key header.
     */
    public const DEFAULT_CLIENT_OPTIONS = [
        'timeout' => 10,
        'max_duration' => 30,
        'max_redirects' => 0,
    ];

    /**
     * @param  HttpClientInterface|null  $client  Overrides the HTTP client for every mailer (handy in tests).
     */
    public function __construct(
        protected Repository $config,
        protected ?HttpClientInterface $client = null,
    ) {}

    /**
     * The .env variable that holds this provider's API key, used in error messages.
     */
    abstract protected function apiKeyEnvironmentVariable(): string;

    /**
     * Resolve the API key: the mailer's own `key` when it defines one, otherwise `services.<name>.key`.
     *
     * A mailer that defines `key` never falls back, so a missing env variable can't silently send
     * through a different account.
     *
     * @param  array<array-key, mixed>  $config
     *
     * @throws MissingApiKeyException
     */
    protected function apiKey(#[\SensitiveParameter] array $config): string
    {
        $key = array_key_exists('key', $config)
            ? $config['key']
            : $this->config->get('services.'.static::name().'.key');

        $key = is_string($key) ? trim($key) : '';

        if ($key === '') {
            throw MissingApiKeyException::forTransport(static::name(), $this->apiKeyEnvironmentVariable());
        }

        return $key;
    }

    /**
     * The HTTP client for the transport: safe defaults plus the mailer's `client` options.
     *
     * @param  array<array-key, mixed>  $config
     *
     * @throws InvalidArgumentException
     */
    protected function httpClient(#[\SensitiveParameter] array $config): HttpClientInterface
    {
        $options = $config['client'] ?? [];

        if (! is_array($options)) {
            throw new InvalidArgumentException(sprintf(
                'The [client] option of the [%s] mailer must be an array of HTTP client options, %s given.',
                static::name(),
                get_debug_type($options),
            ));
        }

        $options = array_replace(self::DEFAULT_CLIENT_OPTIONS, $options);

        return $this->client !== null
            ? $this->client->withOptions($options)
            : HttpClient::create($options);
    }
}
