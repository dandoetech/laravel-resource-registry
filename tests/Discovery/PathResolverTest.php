<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Tests\Discovery;

use DanDoeTech\LaravelResourceRegistry\Discovery\PathResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PathResolverTest extends TestCase
{
    private string $fixturesPath;

    protected function setUp(): void
    {
        $this->fixturesPath = \dirname(__DIR__) . '/Fixtures';
    }

    #[Test]
    public function it_resolves_php_files_from_single_pattern(): void
    {
        $resolver = new PathResolver($this->fixturesPath, ['App/Resources']);

        $files = $resolver->resolve();

        $this->assertNotEmpty($files);

        foreach ($files as $file) {
            $this->assertStringEndsWith('.php', $file);
            $this->assertFileExists($file);
        }
    }

    #[Test]
    public function it_finds_all_php_files_in_directory(): void
    {
        $resolver = new PathResolver($this->fixturesPath, ['App/Resources']);

        $files = $resolver->resolve();
        $basenames = \array_map('basename', $files);

        $this->assertContains('ProductResource.php', $basenames);
        $this->assertContains('CategoryResource.php', $basenames);
        $this->assertContains('AbstractBaseResource.php', $basenames);
        $this->assertContains('NotAResource.php', $basenames);
    }

    #[Test]
    public function it_resolves_wildcard_patterns(): void
    {
        $resolver = new PathResolver($this->fixturesPath, ['Modules/*/Resources']);

        $files = $resolver->resolve();
        $basenames = \array_map('basename', $files);

        $this->assertContains('PostResource.php', $basenames);
    }

    #[Test]
    public function it_merges_multiple_patterns_without_duplicates(): void
    {
        $resolver = new PathResolver($this->fixturesPath, [
            'App/Resources',
            'Modules/*/Resources',
        ]);

        $files = $resolver->resolve();
        $basenames = \array_map('basename', $files);

        $this->assertContains('ProductResource.php', $basenames);
        $this->assertContains('CategoryResource.php', $basenames);
        $this->assertContains('PostResource.php', $basenames);

        // No duplicates
        $this->assertCount(\count(\array_unique($files)), $files);
    }

    #[Test]
    public function it_returns_empty_array_for_non_matching_pattern(): void
    {
        $resolver = new PathResolver($this->fixturesPath, ['NonExistent/Path']);

        $this->assertSame([], $resolver->resolve());
    }

    #[Test]
    public function it_returns_empty_array_for_no_patterns(): void
    {
        $resolver = new PathResolver($this->fixturesPath, []);

        $this->assertSame([], $resolver->resolve());
    }

    #[Test]
    public function it_returns_absolute_paths(): void
    {
        $resolver = new PathResolver($this->fixturesPath, ['App/Resources']);

        foreach ($resolver->resolve() as $file) {
            $this->assertStringStartsWith('/', $file);
            $this->assertSame(\realpath($file), $file);
        }
    }
}
