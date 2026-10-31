<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Tests\Unit;

use Illuminate\Config\Repository;
use Lmottasin\HttpMailer\Drivers\SendGridDriver;
use Lmottasin\HttpMailer\Exceptions\MissingApiKeyException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridApiTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;

final class SendGridDriverTest extends TestCase
{
    private string $method = '';

    private string $url = '';

    private string $body = '';

    /** @var array<string, list<string>> */
    private array $headers = [];

    public function test_is_named_sendgrid(): void
    {
        $this->assertSame('sendgrid', SendGridDriver::name());
    }

    public function test_builds_a_sendgrid_api_transport(): void
    {
        $this->assertInstanceOf(SendgridApiTransport::class, $this->driver()->transport(['key' => 'SG.key']));
    }

    public function test_posts_the_message_to_the_sendgrid_api_with_a_bearer_token(): void
    {
        $this->send($this->driver()->transport(['key' => 'SG.mailer-key']));

        $this->assertSame('POST', $this->method);
        $this->assertSame('https://api.sendgrid.com/v3/mail/send', $this->url);
        $this->assertSame(['Authorization: Bearer SG.mailer-key'], $this->header('authorization'));
        $this->assertStringContainsString('jane@example.com', $this->body);
    }

    public function test_uses_the_eu_endpoint_when_a_region_is_set(): void
    {
        $this->send($this->driver()->transport(['key' => 'SG.key', 'region' => 'eu']));

        $this->assertSame('https://api.eu.sendgrid.com/v3/mail/send', $this->url);
    }

    public function test_reads_the_region_from_services_config(): void
    {
        $this->send($this->driver(['region' => 'eu'])->transport(['key' => 'SG.key']));

        $this->assertSame('https://api.eu.sendgrid.com/v3/mail/send', $this->url);
    }

    public function test_falls_back_to_the_services_key_when_the_mailer_has_none(): void
    {
        $this->send($this->driver(['key' => 'SG.services-key'])->transport([]));

        $this->assertSame(['Authorization: Bearer SG.services-key'], $this->header('authorization'));
    }

    public function test_does_not_fall_back_when_the_mailer_key_is_null(): void
    {
        $this->expectException(MissingApiKeyException::class);
        $this->expectExceptionMessage('SENDGRID_API_KEY');

        $this->driver(['key' => 'SG.services-key'])->transport(['key' => null]);
    }

    public function test_rejects_an_empty_key(): void
    {
        $this->expectException(MissingApiKeyException::class);

        $this->driver()->transport(['key' => '']);
    }

    public function test_rejects_a_missing_key(): void
    {
        $this->expectException(MissingApiKeyException::class);

        $this->driver()->transport([]);
    }

    /**
     * @param  array<string, mixed>  $services
     */
    private function driver(array $services = []): SendGridDriver
    {
        $client = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            $this->capture($method, $url, $options);

            return new MockResponse('', ['http_code' => 202, 'response_headers' => ['x-message-id' => 'abc123']]);
        });

        return new SendGridDriver(new Repository(['services' => ['sendgrid' => $services]]), $client);
    }

    private function send(TransportInterface $transport): void
    {
        $transport->send((new Email)->from('app@example.com')->to('jane@example.com')->subject('Hi')->text('Hello'));
    }

    /**
     * @param  array<array-key, mixed>  $options
     */
    private function capture(string $method, string $url, array $options): void
    {
        $this->method = $method;
        $this->url = $url;
        $this->body = is_string($options['body'] ?? null) ? $options['body'] : '';
        $this->headers = [];

        foreach (is_array($options['normalized_headers'] ?? null) ? $options['normalized_headers'] : [] as $header => $values) {
            $this->headers[(string) $header] = array_values(array_filter(is_array($values) ? $values : [], 'is_string'));
        }
    }

    /**
     * @return list<string>
     */
    private function header(string $name): array
    {
        return $this->headers[$name] ?? [];
    }
}
