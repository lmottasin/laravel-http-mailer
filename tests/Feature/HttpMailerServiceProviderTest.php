<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Tests\Feature;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Foundation\Application;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;
use Lmottasin\HttpMailer\Contracts\TransportFactory;
use Lmottasin\HttpMailer\Drivers\BrevoDriver;
use Lmottasin\HttpMailer\Drivers\SendGridDriver;
use Lmottasin\HttpMailer\Exceptions\MissingApiKeyException;
use Lmottasin\HttpMailer\HttpMailerServiceProvider;
use Lmottasin\HttpMailer\Tests\Fixtures\NullDriver;
use Lmottasin\HttpMailer\Tests\Fixtures\OrderShipped;
use Lmottasin\HttpMailer\Tests\Fixtures\WelcomeMail;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport;
use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridApiTransport;
use Symfony\Component\Mailer\Transport\FailoverTransport;
use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
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

    public function test_adds_default_service_entries_when_the_app_has_none(): void
    {
        $this->assertTrue(config()->has('services.sendgrid.region'));
        $this->assertTrue(config()->has('services.brevo.key'));
    }

    public function test_keeps_a_services_block_the_app_already_defines(): void
    {
        config()->set('services.sendgrid', ['key' => 'SG.app-key']);

        $this->registerProviderAgain();

        // Laravel merges package config one level deep: the app's block wins as a whole.
        $this->assertSame(['key' => 'SG.app-key'], config('services.sendgrid'));
        $this->assertNull(config('services.sendgrid.region'));
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
     * @return array<string, array{0: string}>
     */
    public static function badDriverNames(): array
    {
        return [
            'built-in transport' => ['smtp'],
            'empty' => [' '],
            'dotted' => ['my.api'],
        ];
    }

    #[DataProvider('badDriverNames')]
    public function test_rejects_a_driver_with_an_unsafe_name(string $name): void
    {
        $driver = new class($name) implements TransportFactory
        {
            private static string $driverName = '';

            public function __construct(string $name)
            {
                self::$driverName = $name;
            }

            public static function name(): string
            {
                return self::$driverName;
            }

            /**
             * @param  array<array-key, mixed>  $config
             */
            public function transport(array $config): TransportInterface
            {
                return new NullTransport;
            }
        };

        config()->set('http-mailer.drivers', [$driver::class]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("has the name [{$name}]");

        $this->registerProviderAgain();
    }

    public function test_rejects_two_drivers_with_the_same_name(): void
    {
        config()->set('http-mailer.drivers', [SendGridDriver::class, SendGridDriver::class]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('is used by another driver');

        $this->registerProviderAgain();
    }

    public function test_builds_transports_from_the_mail_managers_current_app(): void
    {
        $manager = app('mail.manager');
        $this->assertInstanceOf(MailManager::class, $manager);

        // Octane hands the mail manager a fresh app per request; per-request config must be used.
        $tenantConfig = new ConfigRepository(['mail' => config('mail'), 'services' => ['sendgrid' => ['key' => 'SG.tenant-key']]]);
        $tenantApp = clone app();
        $tenantApp->instance('config', $tenantConfig);
        $tenantApp->instance(ConfigContract::class, $tenantConfig);
        $tenantApp->instance('view', app('view'));
        $tenantApp->instance('events', app('events'));

        $authorization = [];
        $tenantApp->instance(HttpClientInterface::class, new MockHttpClient(function (string $method, string $url, array $options) use (&$authorization): MockResponse {
            $headers = is_array($options['normalized_headers'] ?? null) ? $options['normalized_headers'] : [];
            $authorization = is_array($headers['authorization'] ?? null) ? $headers['authorization'] : [];

            return new MockResponse('', ['http_code' => 202, 'response_headers' => ['x-message-id' => 'abc']]);
        }));

        $manager->setApplication($tenantApp);
        $manager->forgetMailers();

        $manager->mailer('sendgrid')->getSymfonyTransport()->send(
            (new Email)->from('app@example.com')->to('jane@example.com')->subject('Hi')->text('Hello'),
        );

        $this->assertSame(['Authorization: Bearer SG.tenant-key'], $authorization);
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
