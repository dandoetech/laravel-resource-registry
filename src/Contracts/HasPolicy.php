<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Contracts;

interface HasPolicy
{
    /** @return class-string */
    public function policy(): string;
}
