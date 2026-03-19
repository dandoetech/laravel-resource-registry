<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Resolvers;

use DanDoeTech\LaravelResourceRegistry\Contracts\EloquentComputedResolverInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Handles via: 'count:relation' — uses withCount() for HasMany/BelongsToMany.
 */
final class RelationCountResolver implements EloquentComputedResolverInterface
{
    public function __construct(
        private readonly string $alias,
        private readonly string $relation,
    ) {
    }

    /**
     * @param  Builder<Model> $query
     * @return Builder<Model>
     */
    public function apply(Builder $query): Builder
    {
        return $query->withCount([$this->relation . ' as ' . $this->alias]);
    }

    /**
     * @param  Builder<Model> $query
     * @return Builder<Model>
     */
    public function filter(Builder $query, mixed $value, string $operator = '='): Builder
    {
        /** @var Builder<Model> */
        return $query->having($this->alias, $operator, $value);
    }

    /**
     * @param  Builder<Model> $query
     * @return Builder<Model>
     */
    public function sort(Builder $query, string $direction): Builder
    {
        /** @var Builder<Model> */
        return $query->orderBy($this->alias, $direction);
    }
}
