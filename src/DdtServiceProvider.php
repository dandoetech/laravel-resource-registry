<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry;

use DanDoeTech\LaravelResourceRegistry\Discovery\PathResolver;
use DanDoeTech\LaravelResourceRegistry\Driver\ClassBasedDriver;
use DanDoeTech\ResourceRegistry\Registry\Registry;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class DdtServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ddt_registry.php', 'ddt_registry');

        $this->app->singleton(Registry::class, static function (Application $app): Registry {
            /** @var ConfigRepository $config */
            $config = $app->make('config');

            /** @var list<string> $paths */
            $paths = (array) $config->get('ddt_registry.resource_paths', []);

            $pathResolver = new PathResolver($app->basePath(), $paths);
            $driver = new ClassBasedDriver($pathResolver);

            return new Registry($driver);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/ddt_registry.php' => $this->app->configPath('ddt_registry.php'),
        ], 'ddt-config');
    }
}
