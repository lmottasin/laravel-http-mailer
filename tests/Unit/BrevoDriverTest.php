<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Tests\Unit;

use Illuminate\Config\Repository;
use Lmottasin\HttpMailer\Drivers\BrevoDriver;
use Lmottasin\HttpMailer\Exceptions\MissingApiKeyException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;

final class BrevoDriverTest extends TestCase
{
    private string $method = '';

    private string $url = '';

    private string $body = '';

    /** @var array<string, list<string>> */
    private array $headers = [];

    public function test_is_named_brevo(): void
    {
        $this->assertSame('brevo', BrevoDriver::name());
    }

    public function test_builds_a_brevo_api_transport(): void
    {
        $this->assertInstanceOf(BrevoApiTransport::class, $this->driver()->transport(['key' => 'xkeysib-key']));
    }

    public function test_posts_the_message_to_the_brevo_api_with_the_api_key_header(): void
    {
        $this->send($this->driver()->transport(['key' => 'xkeysib-mailer-key']));

        $this->assertSame('POST', $this->method);
        $this->assertSame('https://api.brevo.com/v3/smtp/email', $this->url);
        $this->assertSame(['api-key: xkeysib-mailer-key'], $this->header('api-key'));
        $this->assertStringContainsString('jane@example.com', $this->body);
    }

    public function test_falls_back_to_the_services_key_when_the_mailer_has_none(): void
    {
        $this->send($this->driver(['key' => 'xkeysib-services-key'])->transport([]));

        $this->assertSame(['api-key: xkeysib-services-key'], $this->header('api-key'));
    }

    public function test_does_not_fall_back_when_the_mailer_key_is_null(): void
    {
        $this->expectException(MissingApiKeyException::class);
        $this->expectExceptionMessage('BREVO_API_KEY');

        $this->driver(['key' => 'xkeysib-services-key'])->transport(['key' => null]);
    }

    public function test_rejects_an_empty_key(): void
    {
        $this->expectException(MissingApiKeyException::class);

        $this->driver()->transport(['key' => '  ']);
    }

    /**
     * @param  array<string, mixed>  $services
     */
    private function driver(array $services = []): BrevoDriver
    {
        $client = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            $this->capture($method, $url, $options);

            return new MockResponse((string) json_encode(['messageId' => '<abc123@smtp-relay.brevo.com>']), ['http_code' => 201]);
        });

        return new BrevoDriver(new Repository(['services' => ['brevo' => $services]]), $client);
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
