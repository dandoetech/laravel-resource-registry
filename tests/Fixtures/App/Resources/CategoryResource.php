<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Tests\Fixtures\App\Resources;

use DanDoeTech\ResourceRegistry\Builder\ResourceBuilder;
use DanDoeTech\ResourceRegistry\Definition\FieldType;
use DanDoeTech\ResourceRegistry\Resource;

final class CategoryResource extends Resource
{
    protected function define(ResourceBuilder $builder): void
    {
        $builder->key('category')
            ->version(1)
            ->label('Category')
            ->field('name', FieldType::String, nullable: false, rules: ['required']);
    }
}
