<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Drivers;

use Illuminate\Contracts\Config\Repository;
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
    protected function apiKey(array $config): string
    {
        $key = array_key_exists('key', $config)
            ? $config['key']
            : $this->config->get('services.'.static::name().'.key');

        if (! is_string($key) || trim($key) === '') {
            throw MissingApiKeyException::forTransport(static::name(), $this->apiKeyEnvironmentVariable());
        }

        return $key;
    }

    /**
     * The HTTP client for the transport, built from the mailer's `client` options (e.g. `timeout`).
     *
     * @param  array<array-key, mixed>  $config
     */
    protected function httpClient(array $config): HttpClientInterface
    {
        if ($this->client !== null) {
            return $this->client;
        }

        $options = $config['client'] ?? [];

        return HttpClient::create(is_array($options) ? $options : []);
    }
}
