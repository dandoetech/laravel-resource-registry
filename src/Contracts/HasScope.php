<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Contracts;

use Closure;

interface HasScope
{
    /** @return class-string|Closure|null */
    public function scope(): string|Closure|null;
}
