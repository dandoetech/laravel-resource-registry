<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Tests\Fixtures\App\Resources;

use DanDoeTech\ResourceRegistry\Builder\ResourceBuilder;
use DanDoeTech\ResourceRegistry\Definition\FieldType;
use DanDoeTech\ResourceRegistry\Resource;

final class ProductResource extends Resource
{
    protected function define(ResourceBuilder $builder): void
    {
        $builder->key('product')
            ->version(1)
            ->label('Product')
            ->timestamps()
            ->field('name', FieldType::String, nullable: false, rules: ['required', 'max:120'])
            ->field('price', FieldType::Float, nullable: false, rules: ['min:0'])
            ->action('create')
            ->action('update');
    }
}
