<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Tests\Driver;

use DanDoeTech\LaravelResourceRegistry\Discovery\PathResolver;
use DanDoeTech\LaravelResourceRegistry\Driver\ClassBasedDriver;
use DanDoeTech\ResourceRegistry\Contracts\ResourceDefinitionInterface;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ClassBasedDriverCacheTest extends TestCase
{
    private string $fixturesPath;

    private CacheRepository $cache;

    protected function setUp(): void
    {
        $this->fixturesPath = \dirname(__DIR__) . '/Fixtures';
        $this->cache = new CacheRepository(new ArrayStore());
    }

    #[Test]
    public function it_caches_discovered_class_names(): void
    {
        $driver = $this->createDriver(['App/Resources'], cacheTtl: 3600);

        // First call triggers filesystem scan and populates cache
        $first = $driver->all();
        $this->assertNotEmpty($first);

        // Verify cache contains the class map
        /** @var array<string, class-string>|null $cached */
        $cached = $this->cache->get('ddt:registry:resources');
        $this->assertIsArray($cached);
        $this->assertArrayHasKey('product', $cached);
        $this->assertArrayHasKey('category', $cached);
    }

    #[Test]
    public function it_returns_same_results_from_cache(): void
    {
        $driver = $this->createDriver(['App/Resources'], cacheTtl: 3600);

        $firstKeys = \array_map(
            static fn (ResourceDefinitionInterface $r): string => $r->getKey(),
            $driver->all(),
        );

        // Create a new driver instance that will read from the same cache
        $driver2 = $this->createDriver(['App/Resources'], cacheTtl: 3600);

        $secondKeys = \array_map(
            static fn (ResourceDefinitionInterface $r): string => $r->getKey(),
            $driver2->all(),
        );

        $this->assertSame($firstKeys, $secondKeys);
    }

    #[Test]
    public function find_works_with_cache(): void
    {
        $driver = $this->createDriver(['App/Resources'], cacheTtl: 3600);

        // Trigger scan + cache
        $driver->all();

        // New driver instance reads from cache
        $driver2 = $this->createDriver(['App/Resources'], cacheTtl: 3600);

        $product = $driver2->find('product');
        $this->assertNotNull($product);
        $this->assertSame('product', $product->getKey());
        $this->assertSame('Product', $product->getLabel());
    }

    #[Test]
    public function cache_invalidation_forces_rescan(): void
    {
        $driver = $this->createDriver(['App/Resources'], cacheTtl: 3600);

        $driver->all();

        // Cache is populated
        $this->assertNotNull($this->cache->get('ddt:registry:resources'));

        // Clear cache
        $this->cache->forget('ddt:registry:resources');

        // New driver instance must re-scan
        $driver2 = $this->createDriver(['App/Resources'], cacheTtl: 3600);
        $resources = $driver2->all();

        $keys = \array_map(
            static fn (ResourceDefinitionInterface $r): string => $r->getKey(),
            $resources,
        );

        $this->assertContains('product', $keys);
        $this->assertContains('category', $keys);

        // Cache is re-populated
        $this->assertNotNull($this->cache->get('ddt:registry:resources'));
    }

    #[Test]
    public function zero_ttl_disables_caching(): void
    {
        $driver = $this->createDriver(['App/Resources'], cacheTtl: 0);

        $driver->all();

        // Cache should not have been populated
        $this->assertNull($this->cache->get('ddt:registry:resources'));
    }

    #[Test]
    public function no_cache_instance_disables_caching(): void
    {
        $driver = new ClassBasedDriver(
            new PathResolver($this->fixturesPath, ['App/Resources']),
            null,
            3600,
        );

        $resources = $driver->all();
        $this->assertNotEmpty($resources);

        // No crash, works fine without cache
    }

    /**
     * @param list<string> $patterns
     */
    private function createDriver(array $patterns, int $cacheTtl = 0): ClassBasedDriver
    {
        return new ClassBasedDriver(
            new PathResolver($this->fixturesPath, $patterns),
            $this->cache,
            $cacheTtl,
        );
    }
}
