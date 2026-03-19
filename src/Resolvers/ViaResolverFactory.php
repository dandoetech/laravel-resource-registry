<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Resolvers;

use DanDoeTech\LaravelResourceRegistry\Contracts\EloquentComputedResolverInterface;

/**
 * Parses a computed field 'via' string and returns the appropriate resolver.
 *
 * Supported formats:
 *   'relation.field'        → RelationFieldResolver  (BelongsTo/HasOne subselect)
 *   'count:relation'        → RelationCountResolver   (withCount)
 *   'pluck:relation.field'  → RelationPluckResolver   (GROUP_CONCAT)
 */
final class ViaResolverFactory
{
    /**
     * @throws \InvalidArgumentException When the via string format is not recognized
     */
    public function create(string $via, string $alias): EloquentComputedResolverInterface
    {
        if (\str_starts_with($via, 'count:')) {
            return $this->createCount($via, $alias);
        }

        if (\str_starts_with($via, 'pluck:')) {
            return $this->createPluck($via, $alias);
        }

        return $this->createRelationField($via, $alias);
    }

    private function createCount(string $via, string $alias): RelationCountResolver
    {
        $relation = \substr($via, 6); // strip 'count:'

        if ($relation === '') {
            throw new \InvalidArgumentException("Invalid via format: '{$via}'. Expected 'count:relation'.");
        }

        return new RelationCountResolver($alias, $relation);
    }

    private function createPluck(string $via, string $alias): RelationPluckResolver
    {
        $rest = \substr($via, 6); // strip 'pluck:'
        $dotPos = \strpos($rest, '.');

        if ($dotPos === false || $dotPos === 0 || $dotPos === \strlen($rest) - 1) {
            throw new \InvalidArgumentException("Invalid via format: '{$via}'. Expected 'pluck:relation.field'.");
        }

        $relation = \substr($rest, 0, $dotPos);
        $field = \substr($rest, $dotPos + 1);

        return new RelationPluckResolver($alias, $relation, $field);
    }

    private function createRelationField(string $via, string $alias): RelationFieldResolver
    {
        $dotPos = \strpos($via, '.');

        if ($dotPos === false || $dotPos === 0 || $dotPos === \strlen($via) - 1) {
            throw new \InvalidArgumentException("Invalid via format: '{$via}'. Expected 'relation.field'.");
        }

        $relation = \substr($via, 0, $dotPos);
        $field = \substr($via, $dotPos + 1);

        return new RelationFieldResolver($relation, $field);
    }
}
