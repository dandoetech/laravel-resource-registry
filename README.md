# Laravel Resource Registry

Laravel bridge for the DanDoeTech Resource Registry. Auto-discovers Resource classes, binds the Registry as a singleton, and provides capability interfaces for Eloquent integration.

This is the central package — install it first, then add consumer packages as needed.

## Installation

```bash
composer require dandoetech/laravel-resource-registry
```

The service provider is auto-discovered. Publish the config:

```bash
php artisan vendor:publish --tag=ddt-config
```

## Quick Start

### 1. Define a Resource

Create a Resource class in `app/Resources/`:

```php
<?php

declare(strict_types=1);

namespace App\Resources;

use DanDoeTech\ResourceRegistry\Resource;
use DanDoeTech\ResourceRegistry\Builder\ResourceBuilder;
use DanDoeTech\ResourceRegistry\Definition\FieldType;
use DanDoeTech\LaravelResourceRegistry\Contracts\HasEloquentModel;
use DanDoeTech\LaravelResourceRegistry\Contracts\HasPolicy;

class ProductResource extends Resource implements HasEloquentModel, HasPolicy
{
    public function model(): string { return \App\Models\Product::class; }
    public function policy(): string { return \App\Policies\ProductPolicy::class; }

    protected function define(ResourceBuilder $b): void
    {
        $b->key('product')
          ->label('Product')
          ->timestamps()
          ->field('name', FieldType::String, nullable: false, rules: ['required', 'max:120'])
          ->field('price', FieldType::Float, nullable: false, rules: ['required', 'numeric', 'min:0'])
          ->field('category_id', FieldType::Integer, nullable: false)
          ->belongsTo('category', foreignKey: 'category_id')
          ->hasMany('reviews')
          ->computed('category_name', FieldType::String, via: 'category.name')
          ->computed('orders_count', FieldType::Integer, via: 'count:orders')
          ->filterable(['name', 'price', 'category_id', 'category_name'])
          ->sortable(['name', 'price', 'created_at', 'orders_count'])
          ->searchable(['name'])
          ->action('create')
          ->action('update')
          ->action('delete');
    }
}
```

### 2. Use the Registry

The Registry is bound as a singleton. Inject or resolve it anywhere:

```php
use DanDoeTech\ResourceRegistry\Registry\Registry;

$registry = app(Registry::class);

$product = $registry->getResource('product');
echo $product->getLabel(); // "Product"

foreach ($registry->all() as $resource) {
    echo $resource->getKey();
}
```

Resources are auto-discovered from configured paths on first access.

## Configuration

`config/ddt_registry.php`:

```php
return [
    // Directories scanned for Resource classes (relative to base_path)
    'resource_paths' => [
        'app/Resources',
        'Modules/*/Resources',
    ],
];
```

> **Note:** The API route prefix is configured in `config/ddt_api.php` (from [`laravel-generic-api`](https://github.com/dandoetech/laravel-generic-api)), not here. This config only controls resource discovery.

Glob patterns are supported — `Modules/*/Resources` discovers resources across all module directories.

## Related Packages

This package provides the registry and resource scanning. For a complete setup, you'll also need:

| Package | Purpose |
|---------|---------|
| [`laravel-generic-api`](https://github.com/dandoetech/laravel-generic-api) | CRUD endpoints (GET/POST/PATCH/DELETE) for all registered resources |
| [`laravel-model-meta`](https://github.com/dandoetech/laravel-model-meta) | Generate Eloquent Models + Migrations from registry definitions |
| [`laravel-openapi-generator`](https://github.com/dandoetech/laravel-openapi-generator) | Auto-generate OpenAPI 3.1 spec from registry |
| [`laravel-bff`](https://github.com/dandoetech/laravel-bff) | Frontend metadata endpoints (fields, forms, tables, permissions) |
| [`filament-registry-bridge`](https://github.com/dandoetech/filament-registry-bridge) | Generate Filament admin panel from registry definitions |

## Capability Interfaces

Framework-specific capabilities are opt-in via small interfaces. Implement only what you need:

```php
// Link resource to an Eloquent model
interface HasEloquentModel
{
    /** @return class-string<Model> */
    public function model(): string;
}

// Link resource to a Laravel policy
interface HasPolicy
{
    /** @return class-string */
    public function policy(): string;
}

// Scope queries to the authenticated user's records
interface HasOwnerScope
{
    public function ownerKey(): string;
}

// @deprecated — use HasOwnerScope or Policies instead (removed in v1.0)
interface HasScope
{
    /** @return class-string|Closure|null */
    public function scope(): string|Closure|null;
}
```

Bridge packages discover capabilities via `instanceof`:

```php
$modelClass = $resource instanceof HasEloquentModel
    ? $resource->model()
    : throw new \RuntimeException("No model for '{$resource->getKey()}'");
```

## Computed Field Resolvers

For resources with computed fields using `via` syntax, resolvers are built automatically:

| Via syntax | Resolver | Example |
|---|---|---|
| `relation.field` | `RelationFieldResolver` (subselect) | `category.name` |
| `count:relation` | `RelationCountResolver` (withCount) | `count:orders` |
| `pluck:relation.field` | `RelationPluckResolver` (GROUP_CONCAT) | `pluck:tags.name` |

For custom logic, implement `EloquentComputedResolverInterface`:

```php
use DanDoeTech\LaravelResourceRegistry\Contracts\EloquentComputedResolverInterface;
use Illuminate\Database\Eloquent\Builder;

class CompletedOrdersCount implements EloquentComputedResolverInterface
{
    public function apply(Builder $query): Builder
    {
        return $query->withCount(['orders as completed_orders' => fn ($q) =>
            $q->where('status', 'completed')
        ]);
    }

    public function filter(Builder $query, mixed $value, string $operator = '='): Builder
    {
        return $query->having('completed_orders', $operator, $value);
    }

    public function sort(Builder $query, string $direction): Builder
    {
        return $query->orderBy('completed_orders', $direction);
    }
}
```

Reference it in your resource:

```php
->computed('completed_orders', FieldType::Integer, resolver: CompletedOrdersCount::class)
```

## API Overview

| Class | Purpose |
|---|---|
| `DdtServiceProvider` | Binds Registry singleton, publishes config |
| `ClassBasedDriver` | Scans configured paths for Resource classes |
| `PathResolver` | Resolves glob patterns to PHP file paths |
| `HasEloquentModel` | Capability: link resource to Eloquent model |
| `HasPolicy` | Capability: link resource to Laravel policy |
| `HasOwnerScope` | Capability: scope queries to authenticated user |
| `HasScope` | *(deprecated)* Capability: apply query scope |
| `EloquentComputedResolverInterface` | Contract for custom computed field query logic |
| `ViaResolverFactory` | Creates resolvers from `via` syntax strings |

## Testing

```bash
composer install
composer test        # PHPUnit (Orchestra Testbench)
composer qa          # cs:check + phpstan + test
```

## License

MIT
