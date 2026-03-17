<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelResourceRegistry\Contracts;

interface HasOwnerScope
{
    /**
     * The foreign key column that identifies the owner (authenticated user).
     * Used to scope queries: WHERE {ownerKey} = auth()->id()
     */
    public function ownerKey(): string;
}
