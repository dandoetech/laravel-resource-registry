<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Tests\Integration;

use DanDoeTech\LaravelResourceRegistry\DdtServiceProvider;
use DanDoeTech\ResourceRegistry\Registry\Registry;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class DdtServiceProviderTest extends TestCase
{
    /** @return list<class-string<\Illuminate\Support\ServiceProvider>> */
    protected function getPackageProviders($app): array
    {
        return [DdtServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $fixturesPath = \dirname(__DIR__) . '/Fixtures';

        $app['config']->set('ddt.resource_paths', [
            $fixturesPath . '/App/Resources',
            $fixturesPath . '/Modules/*/Resources',
        ]);

        // Override basePath to '' so absolute fixture paths resolve correctly
        $app['config']->set('ddt.api_prefix', 'api');
    }

    #[Test]
    public function it_registers_registry_as_singleton(): void
    {
        $first = $this->app->make(Registry::class);
        $second = $this->app->make(Registry::class);

        $this->assertInstanceOf(Registry::class, $first);
        $this->assertSame($first, $second);
    }

    #[Test]
    public function it_discovers_resources_from_configured_paths(): void
    {
        $registry = $this->app->make(Registry::class);

        $product = $registry->getResource('product');
        $this->assertNotNull($product);
        $this->assertSame('Product', $product->getLabel());

        $category = $registry->getResource('category');
        $this->assertNotNull($category);

        $post = $registry->getResource('post');
        $this->assertNotNull($post);
        $this->assertSame('Post', $post->getLabel());
    }

    #[Test]
    public function it_returns_all_discovered_resources(): void
    {
        $registry = $this->app->make(Registry::class);
        $all = $registry->all();

        $keys = \array_map(static fn ($r) => $r->getKey(), $all);

        $this->assertContains('product', $keys);
        $this->assertContains('category', $keys);
        $this->assertContains('post', $keys);
    }

    #[Test]
    public function it_merges_default_config(): void
    {
        $this->assertSame('api', $this->app['config']->get('ddt.api_prefix'));
        $this->assertSame(25, $this->app['config']->get('ddt.pagination.per_page'));
        $this->assertSame(200, $this->app['config']->get('ddt.pagination.max_per_page'));
    }

    #[Test]
    public function it_returns_null_for_unknown_resource(): void
    {
        $registry = $this->app->make(Registry::class);

        $this->assertNull($registry->getResource('nonexistent'));
    }
}
