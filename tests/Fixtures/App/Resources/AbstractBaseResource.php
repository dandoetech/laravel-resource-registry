<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Tests\Fixtures\App\Resources;

use DanDoeTech\ResourceRegistry\Builder\ResourceBuilder;
use DanDoeTech\ResourceRegistry\Resource;

abstract class AbstractBaseResource extends Resource
{
    protected function define(ResourceBuilder $builder): void
    {
        $builder->key('abstract_base');
    }
}
