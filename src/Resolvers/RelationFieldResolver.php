<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Resolvers;

use DanDoeTech\LaravelResourceRegistry\Contracts\EloquentComputedResolver;
use Illuminate\Database\Eloquent\Builder;

/**
 * Handles via: 'relation.field' — adds a correlated subselect for BelongsTo/HasOne.
 *
 * Example: via 'category.name' on a Product with category_id
 * produces a subselect like:
 *   (SELECT name FROM categories WHERE categories.id = products.category_id) AS category_name
 */
final class RelationFieldResolver implements EloquentComputedResolver
{
    public function __construct(
        private readonly string $alias,
        private readonly string $relation,
        private readonly string $field,
    ) {
    }

    public function apply(Builder $query): Builder
    {
        return $query->withAggregate($this->relation, $this->field);
    }

    public function filter(Builder $query, mixed $value, string $operator = '='): Builder
    {
        $qualifiedAlias = $this->relation . '_' . $this->field;

        return $query->having($qualifiedAlias, $operator, $value);
    }

    public function sort(Builder $query, string $direction): Builder
    {
        $qualifiedAlias = $this->relation . '_' . $this->field;

        return $query->orderBy($qualifiedAlias, $direction);
    }
}
