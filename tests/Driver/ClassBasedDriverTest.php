<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Tests\Driver;

use DanDoeTech\LaravelResourceRegistry\Discovery\PathResolver;
use DanDoeTech\LaravelResourceRegistry\Driver\ClassBasedDriver;
use DanDoeTech\ResourceRegistry\Contracts\ResourceDefinitionInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ClassBasedDriverTest extends TestCase
{
    private string $fixturesPath;

    protected function setUp(): void
    {
        $this->fixturesPath = \dirname(__DIR__) . '/Fixtures';
    }

    #[Test]
    public function it_discovers_resource_classes(): void
    {
        $driver = $this->createDriver(['App/Resources']);

        $resources = $driver->all();

        $keys = \array_map(
            static fn (ResourceDefinitionInterface $r): string => $r->getKey(),
            $resources,
        );

        $this->assertContains('product', $keys);
        $this->assertContains('category', $keys);
    }

    #[Test]
    public function it_skips_abstract_classes(): void
    {
        $driver = $this->createDriver(['App/Resources']);

        $keys = \array_map(
            static fn (ResourceDefinitionInterface $r): string => $r->getKey(),
            $driver->all(),
        );

        $this->assertNotContains('abstract_base', $keys);
    }

    #[Test]
    public function it_skips_non_resource_classes(): void
    {
        $driver = $this->createDriver(['App/Resources']);

        $keys = \array_map(
            static fn (ResourceDefinitionInterface $r): string => $r->getKey(),
            $driver->all(),
        );

        $this->assertNotContains('not_a_resource', $keys);
    }

    #[Test]
    public function it_finds_resource_by_key(): void
    {
        $driver = $this->createDriver(['App/Resources']);

        $product = $driver->find('product');

        $this->assertNotNull($product);
        $this->assertSame('product', $product->getKey());
        $this->assertSame('Product', $product->getLabel());
    }

    #[Test]
    public function it_returns_null_for_unknown_key(): void
    {
        $driver = $this->createDriver(['App/Resources']);

        $this->assertNull($driver->find('nonexistent'));
    }

    #[Test]
    public function it_discovers_resources_from_wildcard_patterns(): void
    {
        $driver = $this->createDriver(['Modules/*/Resources']);

        $post = $driver->find('post');

        $this->assertNotNull($post);
        $this->assertSame('Post', $post->getLabel());
    }

    #[Test]
    public function it_merges_resources_from_multiple_patterns(): void
    {
        $driver = $this->createDriver([
            'App/Resources',
            'Modules/*/Resources',
        ]);

        $resources = $driver->all();

        $keys = \array_map(
            static fn (ResourceDefinitionInterface $r): string => $r->getKey(),
            $resources,
        );

        $this->assertContains('product', $keys);
        $this->assertContains('category', $keys);
        $this->assertContains('post', $keys);
    }

    #[Test]
    public function it_caches_scan_result(): void
    {
        $driver = $this->createDriver(['App/Resources']);

        $first = $driver->all();
        $second = $driver->all();

        $this->assertSame(\count($first), \count($second));

        // Same instances — scan happened only once
        foreach ($first as $i => $resource) {
            $this->assertSame($resource, $second[$i]);
        }
    }

    #[Test]
    public function it_preserves_resource_definition_details(): void
    {
        $driver = $this->createDriver(['App/Resources']);

        $product = $driver->find('product');

        $this->assertNotNull($product);
        $this->assertSame(1, $product->getVersion());
        $this->assertTrue($product->isTimestamped());
        $this->assertNotNull($product->getField('name'));
        $this->assertNotNull($product->getField('price'));

        $actionNames = \array_map(
            static fn ($a) => $a->getName(),
            $product->getActions(),
        );
        $this->assertContains('create', $actionNames);
        $this->assertContains('update', $actionNames);
    }

    #[Test]
    public function it_returns_empty_for_no_matching_paths(): void
    {
        $driver = $this->createDriver(['NonExistent/Path']);

        $this->assertSame([], $driver->all());
        $this->assertNull($driver->find('anything'));
    }

    /**
     * @param list<string> $patterns
     */
    private function createDriver(array $patterns): ClassBasedDriver
    {
        return new ClassBasedDriver(
            new PathResolver($this->fixturesPath, $patterns),
        );
    }
}
