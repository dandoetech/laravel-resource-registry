<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Tests\Fixtures\App\Resources;

final class NotAResource
{
    public function getKey(): string
    {
        return 'not_a_resource';
    }
}
