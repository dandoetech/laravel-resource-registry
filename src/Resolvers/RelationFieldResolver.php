<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Resolvers;

use DanDoeTech\LaravelResourceRegistry\Contracts\EloquentComputedResolverInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Handles via: 'relation.field' — adds a correlated subselect for BelongsTo/HasOne.
 */
final class RelationFieldResolver implements EloquentComputedResolverInterface
{
    public function __construct(
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
        return $query->withAggregate($this->relation, $this->field);
    }

    /**
     * @param  Builder<Model> $query
     * @return Builder<Model>
     */
    public function filter(Builder $query, mixed $value, string $operator = '='): Builder
    {
        $qualifiedAlias = $this->relation . '_' . $this->field;

        /** @var Builder<Model> */
        return $query->having($qualifiedAlias, $operator, $value);
    }

    /**
     * @param  Builder<Model> $query
     * @return Builder<Model>
     */
    public function sort(Builder $query, string $direction): Builder
    {
        $qualifiedAlias = $this->relation . '_' . $this->field;

        /** @var Builder<Model> */
        return $query->orderBy($qualifiedAlias, $direction);
    }
}
