<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry;

use DanDoeTech\LaravelResourceRegistry\Discovery\PathResolver;
use DanDoeTech\LaravelResourceRegistry\Driver\ClassBasedDriver;
use DanDoeTech\ResourceRegistry\Contracts\RegistryDriverInterface;
use DanDoeTech\ResourceRegistry\Registry\Registry;
use Illuminate\Support\ServiceProvider;

final class DdtServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ddt.php', 'ddt');

        $this->app->singleton(Registry::class, function ($app): Registry {
            /** @var list<string> $paths */
            $paths = (array) $app['config']->get('ddt.resource_paths', []);

            $pathResolver = new PathResolver($app->basePath(), $paths);
            $driver = new ClassBasedDriver($pathResolver);

            return new Registry($driver);
        });

        $this->app->alias(Registry::class, RegistryDriverInterface::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/ddt.php' => $this->app->configPath('ddt.php'),
        ], 'ddt-config');
    }
}
