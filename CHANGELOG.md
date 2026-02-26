# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- `DdtServiceProvider` binding `Registry` as a singleton with `ClassBasedDriver`
- `ClassBasedDriver` scanning configured paths for `Resource` subclasses with lazy-load caching
- `PathResolver` with glob-pattern file discovery (supports absolute paths, relative paths, wildcards)
- Capability interfaces: `HasEloquentModel`, `HasPolicy`, `HasScope`
- `EloquentComputedResolver` interface with `apply()`, `filter()`, `sort()` methods
- `ViaResolverFactory` parsing `via` syntax into resolver instances
- `RelationFieldResolver` for `relation.field` subselects (BelongsTo/HasOne)
- `RelationCountResolver` for `count:relation` aggregates (HasMany/BelongsToMany)
- `RelationPluckResolver` for `pluck:relation.field` GROUP_CONCAT aggregates
- `ddt_registry.php` config with `api_prefix` and `resource_paths` options
- Config publishing via `ddt-config` tag
