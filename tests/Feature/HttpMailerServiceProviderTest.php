<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Tests\Feature;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;
use Lmottasin\HttpMailer\Drivers\BrevoDriver;
use Lmottasin\HttpMailer\Drivers\SendGridDriver;
use Lmottasin\HttpMailer\Exceptions\MissingApiKeyException;
use Lmottasin\HttpMailer\HttpMailerServiceProvider;
use Lmottasin\HttpMailer\Tests\Fixtures\NullDriver;
use Lmottasin\HttpMailer\Tests\Fixtures\OrderShipped;
use Lmottasin\HttpMailer\Tests\Fixtures\WelcomeMail;
use Orchestra\Testbench\TestCase;
use stdClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport;
use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridApiTransport;
use Symfony\Component\Mailer\Transport\FailoverTransport;
use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpMailerServiceProviderTest extends TestCase
{
    /** @var list<array{url: string, body: string}> */
    private array $requests = [];

    /**
     * @param  Application  $app
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [HttpMailerServiceProvider::class];
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('mail.from', ['address' => 'app@example.com', 'name' => 'App']);
        $app['config']->set('services.sendgrid.key', 'SG.test-key');
        $app['config']->set('services.brevo.key', 'xkeysib-test-key');
    }

    public function test_registers_the_sendgrid_and_brevo_mailers(): void
    {
        $this->assertInstanceOf(SendgridApiTransport::class, Mail::mailer('sendgrid')->getSymfonyTransport());
        $this->assertInstanceOf(BrevoApiTransport::class, Mail::mailer('brevo')->getSymfonyTransport());
    }

    public function test_adds_mailer_entries_to_the_mail_config(): void
    {
        $this->assertSame(['transport' => 'sendgrid'], config('mail.mailers.sendgrid'));
        $this->assertSame(['transport' => 'brevo'], config('mail.mailers.brevo'));
    }

    public function test_merges_default_service_credentials_without_overriding_the_app(): void
    {
        $this->assertSame('SG.test-key', config('services.sendgrid.key'));
        $this->assertTrue(config()->has('services.sendgrid.region'));
    }

    public function test_keeps_a_mailer_the_app_already_defines(): void
    {
        $custom = ['transport' => 'sendgrid', 'key' => 'SG.custom-key', 'region' => 'eu'];
        config()->set('mail.mailers.sendgrid', $custom);

        $this->registerProviderAgain();

        $this->assertSame($custom, config('mail.mailers.sendgrid'));
    }

    public function test_does_not_load_the_mail_manager_while_booting(): void
    {
        $this->assertFalse(app()->resolved('mail.manager'));
    }

    public function test_sends_a_mailable_without_code_changes(): void
    {
        $this->fakeHttp();

        Mail::mailer('sendgrid')->to('jane@example.com')->send(new WelcomeMail);

        $this->assertCount(1, $this->requests);
        $this->assertSame('https://api.sendgrid.com/v3/mail/send', $this->requests[0]['url']);
        $this->assertStringContainsString('Welcome aboard', $this->requests[0]['body']);
    }

    public function test_sends_a_notification_without_code_changes(): void
    {
        $this->fakeHttp();
        config()->set('mail.default', 'brevo');

        Notification::route('mail', 'jane@example.com')->notify(new OrderShipped);

        $this->assertCount(1, $this->requests);
        $this->assertSame('https://api.brevo.com/v3/smtp/email', $this->requests[0]['url']);
        $this->assertStringContainsString('Your order shipped', $this->requests[0]['body']);
    }

    public function test_works_inside_a_failover_mailer(): void
    {
        config()->set('mail.mailers.api', ['transport' => 'failover', 'mailers' => ['sendgrid', 'brevo']]);

        $this->assertInstanceOf(FailoverTransport::class, Mail::mailer('api')->getSymfonyTransport());
    }

    public function test_reports_a_missing_api_key_clearly(): void
    {
        config()->set('services.brevo.key', null);

        $this->expectException(MissingApiKeyException::class);
        $this->expectExceptionMessage('BREVO_API_KEY');

        Mail::mailer('brevo')->getSymfonyTransport();
    }

    public function test_registers_custom_drivers_from_config(): void
    {
        config()->set('http-mailer.drivers', [SendGridDriver::class, BrevoDriver::class, NullDriver::class]);

        $this->registerProviderAgain();

        $this->assertSame(['transport' => 'null-api'], config('mail.mailers.null-api'));
        $this->assertInstanceOf(NullTransport::class, Mail::mailer('null-api')->getSymfonyTransport());
    }

    public function test_rejects_a_driver_that_does_not_implement_the_contract(): void
    {
        config()->set('http-mailer.drivers', [stdClass::class]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must implement');

        $this->registerProviderAgain();
    }

    /**
     * Run the provider against the config the test just set, as if the app had booted with it.
     */
    private function registerProviderAgain(): void
    {
        $provider = new HttpMailerServiceProvider(app());
        $provider->register();
        $provider->boot();
    }

    private function fakeHttp(): void
    {
        app()->instance(HttpClientInterface::class, new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            $this->requests[] = ['url' => $url, 'body' => is_string($options['body'] ?? null) ? $options['body'] : ''];

            return str_contains($url, 'brevo')
                ? new MockResponse((string) json_encode(['messageId' => '<abc@brevo>']), ['http_code' => 201])
                : new MockResponse('', ['http_code' => 202, 'response_headers' => ['x-message-id' => 'abc']]);
        }));
    }
}
