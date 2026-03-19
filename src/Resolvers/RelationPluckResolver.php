<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Resolvers;

use DanDoeTech\LaravelResourceRegistry\Contracts\EloquentComputedResolverInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;

/**
 * Handles via: 'pluck:relation.field' — GROUP_CONCAT for BelongsToMany.
 */
final class RelationPluckResolver implements EloquentComputedResolverInterface
{
    public function __construct(
        private readonly string $alias,
        private readonly string $relation,
        private readonly string $field,
    ) {
    }

    /**
     * @param  Builder<Model> $query
     * @return Builder<Model>
     */
    public function apply(Builder $query): Builder
    {
        $expression = new Expression(
            'GROUP_CONCAT(' . $query->getGrammar()->wrap($this->field) . " SEPARATOR ', ')",
        );

        return $query->withAggregate($this->relation, $expression);
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
