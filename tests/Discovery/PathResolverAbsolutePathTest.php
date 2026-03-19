<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Tests\Discovery;

use DanDoeTech\LaravelResourceRegistry\Discovery\PathResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PathResolverAbsolutePathTest extends TestCase
{
    private string $fixturesPath;

    protected function setUp(): void
    {
        $this->fixturesPath = \dirname(__DIR__) . '/Fixtures';
    }

    #[Test]
    public function resolves_absolute_path_without_prepending_base_path(): void
    {
        $absolutePath = \realpath($this->fixturesPath . '/App/Resources');
        self::assertIsString($absolutePath);

        // Using absolute path - should NOT prepend basePath
        $resolver = new PathResolver('/some/irrelevant/base', [$absolutePath]);

        $files = $resolver->resolve();

        self::assertNotEmpty($files);
        $basenames = \array_map('basename', $files);
        self::assertContains('ProductResource.php', $basenames);
    }

    #[Test]
    public function mixes_absolute_and_relative_patterns(): void
    {
        $absolutePath = \realpath($this->fixturesPath . '/Modules/Blog/Resources');
        self::assertIsString($absolutePath);

        $resolver = new PathResolver($this->fixturesPath, [
            'App/Resources',     // relative
            $absolutePath,       // absolute
        ]);

        $files = $resolver->resolve();
        $basenames = \array_map('basename', $files);

        self::assertContains('ProductResource.php', $basenames);
        self::assertContains('PostResource.php', $basenames);
    }

    #[Test]
    public function deduplicates_files_from_overlapping_patterns(): void
    {
        $absolutePath = \realpath($this->fixturesPath . '/App/Resources');
        self::assertIsString($absolutePath);

        $resolver = new PathResolver($this->fixturesPath, [
            'App/Resources',     // relative - same directory
            $absolutePath,       // absolute - same directory
        ]);

        $files = $resolver->resolve();

        // Should have no duplicates
        self::assertCount(\count(\array_unique($files)), $files);
    }
}
