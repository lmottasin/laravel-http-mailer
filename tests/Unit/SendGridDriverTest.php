<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Tests\Unit;

use Lmottasin\HttpMailer\Contracts\MailerDriver;
use Lmottasin\HttpMailer\Drivers\SendGridDriver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridApiTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class SendGridDriverTest extends TestCase
{
    public function test_exposes_the_sendgrid_driver_name(): void
    {
        $this->assertSame('sendgrid', (new SendGridDriver)->name());
    }

    public function test_keeps_the_name_constant_in_sync_with_the_name_method(): void
    {
        $this->assertSame(SendGridDriver::NAME, (new SendGridDriver)->name());
    }

    public function test_implements_the_mailer_driver_contract(): void
    {
        $this->assertInstanceOf(MailerDriver::class, new SendGridDriver);
    }

    public function test_builds_a_symfony_sendgrid_api_transport_from_a_config_array(): void
    {
        $transport = (new SendGridDriver)->transport(['key' => 'SG.test-key']);

        $this->assertInstanceOf(SendgridApiTransport::class, $transport);
        $this->assertInstanceOf(TransportInterface::class, $transport);
    }
}
