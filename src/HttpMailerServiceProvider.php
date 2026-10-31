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
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/http-mailer.php', 'http-mailer');
        $this->mergeConfigFrom(__DIR__.'/../config/services.php', 'services');

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
                    fn (array $config): TransportInterface => $this->factory($driver)->transport($config),
                );
            }
        });
    }

    /**
     * The transport factories listed in `http-mailer.drivers`.
     *
     * @return list<class-string<TransportFactory>>
     */
    protected function drivers(): array
    {
        $drivers = $this->config()->get('http-mailer.drivers', []);

        if (! is_array($drivers)) {
            throw new InvalidArgumentException('The [http-mailer.drivers] config value must be an array of class names.');
        }

        foreach ($drivers as $driver) {
            if (! is_string($driver) || ! is_a($driver, TransportFactory::class, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Mail driver [%s] must implement %s.',
                    is_string($driver) ? $driver : get_debug_type($driver),
                    TransportFactory::class,
                ));
            }
        }

        /** @var list<class-string<TransportFactory>> */
        return array_values($drivers);
    }

    /**
     * @param  class-string<TransportFactory>  $driver
     */
    protected function factory(string $driver): TransportFactory
    {
        $factory = $this->app->make($driver);

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
