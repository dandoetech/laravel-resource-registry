<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface EloquentComputedResolver
{
    /**
     * Modify the query to make the computed field available.
     *
     * @param  Builder<Model> $query
     * @return Builder<Model>
     */
    public function apply(Builder $query): Builder;

    /**
     * Custom filter logic if default having() is insufficient.
     *
     * @param  Builder<Model> $query
     * @return Builder<Model>
     */
    public function filter(Builder $query, mixed $value, string $operator = '='): Builder;

    /**
     * Custom sort logic.
     *
     * @param  Builder<Model> $query
     * @return Builder<Model>
     */
    public function sort(Builder $query, string $direction): Builder;
}
