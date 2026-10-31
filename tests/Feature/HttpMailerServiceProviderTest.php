<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Tests\Feature;

use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Mail;
use Lmottasin\HttpMailer\HttpMailerServiceProvider;
use Orchestra\Testbench\TestCase;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport;
use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridApiTransport;

final class HttpMailerServiceProviderTest extends TestCase
{
    /**
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [HttpMailerServiceProvider::class];
    }

    public function test_registers_the_sendgrid_mailer(): void
    {
        config()->set('http-mailer.sendgrid.key', 'SG.test-key');

        $mailer = Mail::mailer('sendgrid');

        $this->assertInstanceOf(Mailer::class, $mailer);
        $this->assertInstanceOf(SendgridApiTransport::class, $mailer->getSymfonyTransport());
    }

    public function test_registers_the_brevo_mailer(): void
    {
        config()->set('http-mailer.brevo.key', 'xkeysib-test-key');

        $mailer = Mail::mailer('brevo');

        $this->assertInstanceOf(Mailer::class, $mailer);
        $this->assertInstanceOf(BrevoApiTransport::class, $mailer->getSymfonyTransport());
    }

    public function test_seeds_the_mail_mailers_config_entries(): void
    {
        $this->assertSame(['transport' => 'sendgrid'], config('mail.mailers.sendgrid'));
        $this->assertSame(['transport' => 'brevo'], config('mail.mailers.brevo'));
    }

    public function test_merges_the_package_config(): void
    {
        $this->assertArrayHasKey('sendgrid', config('http-mailer'));
        $this->assertArrayHasKey('brevo', config('http-mailer'));
    }
}
