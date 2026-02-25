<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Resolvers;

use DanDoeTech\LaravelResourceRegistry\Contracts\EloquentComputedResolver;
use Illuminate\Database\Eloquent\Builder;

/**
 * Handles via: 'count:relation' — uses withCount() for HasMany/BelongsToMany.
 *
 * Example: via 'count:orders' produces withCount('orders as orders_count').
 */
final class RelationCountResolver implements EloquentComputedResolver
{
    public function __construct(
        private readonly string $alias,
        private readonly string $relation,
    ) {
    }

    public function apply(Builder $query): Builder
    {
        return $query->withCount([$this->relation . ' as ' . $this->alias]);
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
