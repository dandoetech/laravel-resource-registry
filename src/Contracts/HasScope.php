<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Contracts;

use Closure;

/**
 * @deprecated Use HasOwnerScope for owner-based scoping or implement scoping via Policies instead.
 *             Will be removed in v1.0.
 */
interface HasScope
{
    /**
     * @deprecated Use HasOwnerScope for owner-based scoping or implement scoping via Policies instead.
     *
     * @return class-string|Closure|null
     */
    public function scope(): string|Closure|null;
}
