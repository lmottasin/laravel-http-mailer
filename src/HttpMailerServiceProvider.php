<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Lmottasin\HttpMailer\Contracts\MailerDriver;
use Lmottasin\HttpMailer\Drivers\BrevoDriver;
use Lmottasin\HttpMailer\Drivers\SendGridDriver;

class HttpMailerServiceProvider extends ServiceProvider
{
    /** @var list<class-string<MailerDriver>> */
    protected array $drivers = [
        SendGridDriver::class,
        BrevoDriver::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/http-mailer.php', 'http-mailer');

        /** @var ConfigRepository $config */
        $config = $this->app['config'];

        foreach ($this->drivers as $driverClass) {
            $this->app->singleton($driverClass);

            $name = $driverClass::NAME;

            if ($config->has("mail.mailers.{$name}")) {
                continue;
            }

            $config->set("mail.mailers.{$name}", [
                'transport' => $name,
            ]);
        }
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/http-mailer.php' => config_path('http-mailer.php'),
            ], 'http-mailer-config');
        }

        foreach ($this->drivers as $driverClass) {
            Mail::extend(
                $driverClass::NAME,
                fn (array $config) => $this->app->make($driverClass)->transport($config),
            );
        }
    }
}
