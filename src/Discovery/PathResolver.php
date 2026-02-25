<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Discovery;

final class PathResolver
{
    /** @param list<string> $patterns Directory patterns (absolute or relative to basePath) */
    public function __construct(
        private readonly string $basePath,
        private readonly array $patterns,
    ) {
    }

    /**
     * Resolve all glob patterns and return matching PHP file paths.
     *
     * @return list<string>
     */
    public function resolve(): array
    {
        $files = [];

        foreach ($this->patterns as $pattern) {
            if (\str_starts_with($pattern, '/')) {
                $fullPattern = $pattern . '/*.php';
            } else {
                $fullPattern = $this->basePath . '/' . $pattern . '/*.php';
            }

            foreach (\glob($fullPattern) ?: [] as $file) {
                $resolved = \realpath($file);

                if ($resolved !== false) {
                    $files[$resolved] = true;
                }
            }
        }

        return \array_keys($files);
    }
}
