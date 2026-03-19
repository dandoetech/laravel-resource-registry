<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Driver;

use DanDoeTech\LaravelResourceRegistry\Discovery\PathResolver;
use DanDoeTech\ResourceRegistry\Contracts\RegistryDriverInterface;
use DanDoeTech\ResourceRegistry\Contracts\ResourceDefinitionInterface;
use DanDoeTech\ResourceRegistry\Resource;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class ClassBasedDriver implements RegistryDriverInterface
{
    /** @var array<string, ResourceDefinitionInterface>|null */
    private ?array $resources = null;

    private const CACHE_KEY = 'ddt:registry:resources';

    public function __construct(
        private readonly PathResolver $pathResolver,
        private readonly ?CacheRepository $cache = null,
        private readonly int $cacheTtl = 0,
    ) {
    }

    /** @return list<ResourceDefinitionInterface> */
    public function all(): array
    {
        return \array_values($this->scan());
    }

    public function find(string $key): ?ResourceDefinitionInterface
    {
        return $this->scan()[$key] ?? null;
    }

    /**
     * Scan configured paths once, instantiate Resource subclasses, index by key.
     *
     * When caching is enabled (cache instance + TTL > 0), the discovered class
     * names are stored in cache. On subsequent requests the filesystem scan is
     * skipped and classes are instantiated directly from the cached list.
     *
     * @return array<string, ResourceDefinitionInterface>
     */
    private function scan(): array
    {
        if ($this->resources !== null) {
            return $this->resources;
        }

        if ($this->cache !== null && $this->cacheTtl > 0) {
            return $this->resources = $this->scanWithCache();
        }

        return $this->resources = $this->scanFilesystem();
    }

    /**
     * Scan filesystem and cache the discovered class names.
     *
     * @return array<string, ResourceDefinitionInterface>
     */
    private function scanWithCache(): array
    {
        /** @var array<string, class-string>|null $cached */
        $cached = $this->cache?->get(self::CACHE_KEY);

        if (\is_array($cached)) {
            return $this->instantiateFromClassMap($cached);
        }

        $resources = $this->scanFilesystem();

        // Store only the key => className map (serializable strings)
        $classMap = [];
        foreach ($resources as $key => $resource) {
            $classMap[$key] = \get_class($resource);
        }

        $this->cache?->put(self::CACHE_KEY, $classMap, $this->cacheTtl);

        return $resources;
    }

    /**
     * Instantiate Resource objects from a cached class map.
     *
     * @param  array<string, class-string>                $classMap
     * @return array<string, ResourceDefinitionInterface>
     */
    private function instantiateFromClassMap(array $classMap): array
    {
        $resources = [];

        foreach ($classMap as $key => $className) {
            if (!\class_exists($className)) {
                continue;
            }

            $reflection = new \ReflectionClass($className);

            if ($reflection->isAbstract() || !$reflection->isSubclassOf(Resource::class)) {
                continue;
            }

            /** @var Resource $resource */
            $resource = $reflection->newInstance();
            $resources[$key] = $resource;
        }

        return $resources;
    }

    /**
     * Perform the actual filesystem scan to discover Resource classes.
     *
     * @return array<string, ResourceDefinitionInterface>
     */
    private function scanFilesystem(): array
    {
        $resources = [];

        foreach ($this->pathResolver->resolve() as $file) {
            $className = $this->extractClassName($file);

            if ($className === null) {
                continue;
            }

            require_once $file;

            if (!\class_exists($className)) {
                continue;
            }

            $reflection = new \ReflectionClass($className);

            if ($reflection->isAbstract() || !$reflection->isSubclassOf(Resource::class)) {
                continue;
            }

            /** @var Resource $resource */
            $resource = $reflection->newInstance();
            $resources[$resource->getKey()] = $resource;
        }

        return $resources;
    }

    /**
     * Parse a PHP file to extract the fully-qualified class name.
     */
    private function extractClassName(string $file): ?string
    {
        $contents = \file_get_contents($file);

        if ($contents === false) {
            return null;
        }

        $namespace = null;
        $class = null;

        if (\preg_match('/^\s*namespace\s+([^;]+)\s*;/m', $contents, $m)) {
            $namespace = \trim($m[1]);
        }

        if (\preg_match('/^\s*(?:final\s+|abstract\s+|readonly\s+)*class\s+(\w+)/m', $contents, $m)) {
            $class = $m[1];
        }

        if ($class === null) {
            return null;
        }

        return $namespace !== null ? $namespace . '\\' . $class : $class;
    }
}
