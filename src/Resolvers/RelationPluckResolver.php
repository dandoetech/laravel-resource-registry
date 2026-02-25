<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Resolvers;

use DanDoeTech\LaravelResourceRegistry\Contracts\EloquentComputedResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Handles via: 'pluck:relation.field' — GROUP_CONCAT for BelongsToMany.
 *
 * Example: via 'pluck:categories.name' produces a subselect with GROUP_CONCAT
 * that aggregates related values into a comma-separated string.
 */
final class RelationPluckResolver implements EloquentComputedResolver
{
    public function __construct(
        private readonly string $alias,
        private readonly string $relation,
        private readonly string $field,
    ) {
    }

    public function apply(Builder $query): Builder
    {
        return $query->withAggregate($this->relation, DB::raw("GROUP_CONCAT({$this->field} SEPARATOR ', ')"));
    }

    public function filter(Builder $query, mixed $value, string $operator = '='): Builder
    {
        return $query->having($this->alias, $operator, $value);
    }

    public function sort(Builder $query, string $direction): Builder
    {
        return $query->orderBy($this->alias, $direction);
    }
}
