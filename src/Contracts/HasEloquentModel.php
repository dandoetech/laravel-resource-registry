<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Contracts;

use Illuminate\Database\Eloquent\Model;

interface HasEloquentModel
{
    /** @return class-string<Model> */
    public function model(): string;
}
