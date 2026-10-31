<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Tests\Unit;

use Illuminate\Config\Repository;
use InvalidArgumentException;
use Lmottasin\HttpMailer\Drivers\BrevoDriver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Mime\Email;

final class ApiDriverTest extends TestCase
{
    /** @var array<array-key, mixed> */
    private array $options = [];

    public function test_applies_safe_http_client_defaults(): void
    {
        $this->send([]);

        $this->assertEquals(10, $this->options['timeout'] ?? null);
        $this->assertEquals(30, $this->options['max_duration'] ?? null);
        $this->assertSame(0, $this->options['max_redirects'] ?? null);
    }

    public function test_mailer_client_options_override_the_defaults(): void
    {
        $this->send(['client' => ['timeout' => 3, 'max_duration' => 5]]);

        $this->assertEquals(3, $this->options['timeout'] ?? null);
        $this->assertEquals(5, $this->options['max_duration'] ?? null);
        $this->assertSame(0, $this->options['max_redirects'] ?? null);
    }

    public function test_rejects_client_options_that_are_not_an_array(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('[client] option of the [brevo] mailer must be an array');

        $this->send(['client' => 'timeout=3']);
    }

    public function test_trims_whitespace_around_the_key(): void
    {
        $this->send(['key' => "  xkeysib-key \n"]);

        /** @var array<string, list<string>> $headers */
        $headers = $this->options['normalized_headers'] ?? [];
        $this->assertSame(['api-key: xkeysib-key'], $headers['api-key'] ?? []);
    }

    /**
     * @param  array<array-key, mixed>  $config
     */
    private function send(array $config): void
    {
        $client = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            $this->options = $options;

            return new MockResponse((string) json_encode(['messageId' => '<abc@brevo>']), ['http_code' => 201]);
        });

        (new BrevoDriver(new Repository([]), $client))
            ->transport($config + ['key' => 'xkeysib-key'])
            ->send((new Email)->from('app@example.com')->to('jane@example.com')->subject('Hi')->text('Hello'));
    }
}
