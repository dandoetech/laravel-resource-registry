<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Driver;

use DanDoeTech\LaravelResourceRegistry\Discovery\PathResolver;
use DanDoeTech\ResourceRegistry\Contracts\RegistryDriverInterface;
use DanDoeTech\ResourceRegistry\Contracts\ResourceDefinitionInterface;
use DanDoeTech\ResourceRegistry\Resource;

final class ClassBasedDriver implements RegistryDriverInterface
{
    /** @var array<string, ResourceDefinitionInterface>|null */
    private ?array $resources = null;

    public function __construct(
        private readonly PathResolver $pathResolver,
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
     * @return array<string, ResourceDefinitionInterface>
     */
    private function scan(): array
    {
        if ($this->resources !== null) {
            return $this->resources;
        }

        $this->resources = [];

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
            $this->resources[$resource->getKey()] = $resource;
        }

        return $this->resources;
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
