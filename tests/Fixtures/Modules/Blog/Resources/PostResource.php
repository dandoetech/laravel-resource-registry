<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Tests\Fixtures\Modules\Blog\Resources;

use DanDoeTech\ResourceRegistry\Builder\ResourceBuilder;
use DanDoeTech\ResourceRegistry\Definition\FieldType;
use DanDoeTech\ResourceRegistry\Resource;

final class PostResource extends Resource
{
    protected function define(ResourceBuilder $builder): void
    {
        $builder->key('post')
            ->version(1)
            ->label('Post')
            ->timestamps()
            ->field('title', FieldType::String, nullable: false, rules: ['required'])
            ->field('body', FieldType::String, nullable: false)
            ->action('create')
            ->action('update')
            ->action('delete');
    }
}
