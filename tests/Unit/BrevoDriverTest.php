<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Tests\Unit;

use Lmottasin\HttpMailer\Contracts\MailerDriver;
use Lmottasin\HttpMailer\Drivers\BrevoDriver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class BrevoDriverTest extends TestCase
{
    public function test_exposes_the_brevo_driver_name(): void
    {
        $this->assertSame('brevo', (new BrevoDriver)->name());
    }

    public function test_keeps_the_name_constant_in_sync_with_the_name_method(): void
    {
        $this->assertSame(BrevoDriver::NAME, (new BrevoDriver)->name());
    }

    public function test_implements_the_mailer_driver_contract(): void
    {
        $this->assertInstanceOf(MailerDriver::class, new BrevoDriver);
    }

    public function test_builds_a_symfony_brevo_api_transport_from_a_config_array(): void
    {
        $transport = (new BrevoDriver)->transport(['key' => 'xkeysib-test-key']);

        $this->assertInstanceOf(BrevoApiTransport::class, $transport);
        $this->assertInstanceOf(TransportInterface::class, $transport);
    }
}
