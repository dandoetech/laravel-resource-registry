<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface EloquentComputedResolver
{
    /** Modify the query to make the computed field available. */
    public function apply(Builder $query): Builder;

    /** Custom filter logic if default having() is insufficient. */
    public function filter(Builder $query, mixed $value, string $operator = '='): Builder;

    /** Custom sort logic. */
    public function sort(Builder $query, string $direction): Builder;
}
