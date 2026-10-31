<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Lmottasin\HttpMailer\Contracts\TransportFactory;
use Symfony\Component\Mailer\Transport\TransportInterface;

class HttpMailerServiceProvider extends ServiceProvider
{
    /**
     * Transport names Laravel ships with. A custom driver can't take these over.
     */
    public const RESERVED_NAMES = [
        'array', 'failover', 'log', 'mailgun', 'postmark', 'resend',
        'roundrobin', 'sendmail', 'ses', 'ses-v2', 'smtp',
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/http-mailer.php', 'http-mailer');
        $this->mergeConfigFrom(__DIR__.'/../config/services.php', 'services');

        // Mailer entries are added here, not lazily, so they are part of `config:cache`
        // and survive Octane's per-request config. This loads the small driver classes on boot.
        $config = $this->config();

        foreach ($this->drivers() as $driver) {
            $mailer = 'mail.mailers.'.$driver::name();

            if (! $config->has($mailer)) {
                $config->set($mailer, ['transport' => $driver::name()]);
            }
        }
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/http-mailer.php' => config_path('http-mailer.php'),
            ], 'http-mailer-config');
        }

        // Register transports only once the mail manager is needed, not on every request.
        $this->callAfterResolving('mail.manager', function (MailManager $manager): void {
            foreach ($this->drivers() as $driver) {
                $manager->extend(
                    $driver::name(),
                    fn (array $config): TransportInterface => $this->factory($manager, $driver)->transport($config),
                );
            }
        });
    }

    /**
     * The transport factories listed in `http-mailer.drivers`, validated.
     *
     * @return list<class-string<TransportFactory>>
     *
     * @throws InvalidArgumentException
     */
    protected function drivers(): array
    {
        $drivers = $this->config()->get('http-mailer.drivers', []);

        if (! is_array($drivers)) {
            throw new InvalidArgumentException('The [http-mailer.drivers] config value must be an array of class names.');
        }

        $names = [];

        foreach ($drivers as $driver) {
            if (! is_string($driver) || ! is_a($driver, TransportFactory::class, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Mail driver [%s] must implement %s.',
                    is_string($driver) ? $driver : get_debug_type($driver),
                    TransportFactory::class,
                ));
            }

            $names[] = $this->validName($driver, $names);
        }

        /** @var list<class-string<TransportFactory>> */
        return array_values($drivers);
    }

    /**
     * @param  class-string<TransportFactory>  $driver
     * @param  list<string>  $taken
     *
     * @throws InvalidArgumentException
     */
    protected function validName(string $driver, array $taken): string
    {
        $name = $driver::name();

        $problem = match (true) {
            trim($name) === '' => 'is empty',
            str_contains($name, '.') => 'contains a dot',
            in_array($name, self::RESERVED_NAMES, true) => 'is a built-in Laravel transport',
            in_array($name, $taken, true) => 'is used by another driver',
            default => null,
        };

        if ($problem !== null) {
            throw new InvalidArgumentException(sprintf('Mail driver [%s] has the name [%s], which %s.', $driver, $name, $problem));
        }

        return $name;
    }

    /**
     * Resolve the driver from the mail manager's current app, so Octane's per-request app and config are used.
     *
     * @param  class-string<TransportFactory>  $driver
     */
    protected function factory(MailManager $manager, string $driver): TransportFactory
    {
        $factory = $manager->getApplication()->make($driver);

        if (! $factory instanceof TransportFactory) {
            throw new InvalidArgumentException(sprintf('Mail driver [%s] must implement %s.', $driver, TransportFactory::class));
        }

        return $factory;
    }

    protected function config(): Repository
    {
        return $this->app->make(Repository::class);
    }
}
