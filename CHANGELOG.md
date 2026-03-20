# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.0] - 2026-03-19

### Added

- `SchemaMetadataCache`, `SchemaMetadata`, `ParameterMetadata` — static singleton cache for all reflection-derived schema data, eliminating per-request reflection overhead
- `ScimSchema::create(array $args)` factory method with type coercion, replacing the `fill(func_get_args())` pattern
- `SchemaQueryResolver` — extracted query management and `Responsable` implementation from `SchemaMapper`
- `SchemaRequestValidator` — extracted validation and schema instantiation from `SchemaMapper`
- `SchemaPatchOperator` — extracted PATCH operation logic from `SchemaMapper`
- `ScimSchema::getExternalIdColumn()` — configurable database column for SCIM externalId (defaults to `config('scim.external_id_column', 'external_id')`)
- `Repository::getByRouteSlug()` — case-insensitive route slug resolution with backward-compatible fallback
- `extensionUrn` property on `ScimSchemaAttribute` — enables SCIM extension namespace grouping in JSON output
- `EnterpriseUserScimSchema::EXTENSION_URN` constant
- Extension namespace flattening in `SchemaRequestValidator::fromRequest()` for incoming requests
- Config key `scim.external_id_column` for customizing the externalId database column

### Changed

- `ScimSchema::fill()` now has early-return check for already-initialized properties (safe with promoted properties)
- `ScimSchema::fromModel()` uses `create()` factory instead of direct constructor call
- `SchemaMapper::fromRequest()` uses `create()` factory instead of direct constructor call
- `SchemaMapper` is now a thin deprecated facade delegating to the 3 extracted classes
- `Repository::add()` auto-registers route slugs for faster resolution
- `ServiceProvider`, `ListModelsForScim`, `GetModelForScim` use `getByRouteSlug()` instead of `getBySuffix(Str::singular())`
- `GetModelScimAttributesMap` now delegates to `SchemaMetadataCache` instead of using fresh reflection
- `ScimSchema::toArray()` groups attributes with `extensionUrn` under their namespace key

### Deprecated

- `ScimSchema::fill()` — use promoted properties with `create()` instead (old pattern still works)
- `SchemaMapper` — use `SchemaQueryResolver`, `SchemaRequestValidator`, `SchemaPatchOperator` directly

## [0.1.1] - 2026-03-19

### Added

- CRUD persistence for User and Group resources (create, update, delete now save to database)
- `fromModel()`, `toModel()`, `applyToModel()`, `syncRelationships()` on `ScimSchema`
- `GroupMember` value object for SCIM Group member attributes
- `members` property on `GroupScimSchema`
- `relationshipValueKey` on `ScimSchemaAttribute` for relationship mapping
- `Repository::getModelForSchema()` for schema-to-model resolution
- PATCH remove with filter path support (e.g., `members[value eq "123"]`)
- Automatic SCIM-compliant error formatting for all SCIM routes (registered in ServiceProvider)
- `ValidationException` rendered as SCIM 400 with `scimType: "invalidValue"`

### Changed

- `ScimErrorException` now emits RFC 7644 compliant responses (`schemas`, `status`, `scimType`, `detail`) instead of non-standard `errors` object
- ServiceProviderConfig `filter.supported` set to `true` (filtering is implemented)
- ServiceProviderConfig `changePassword.supported` set to `false` (not implemented)
- ServiceProviderConfig `filter.maxResults` default changed from 10 to 100
- Gate authorization moved before input parsing in `CreateScimModel` and `UpdateScimModel`

### Fixed

- `fill()` now handles `func_get_args()` array wrapping correctly
- `addValueToPath` now merges list items and writes back with `replace: true` (PATCH add for members was silently lost)
- PATCH remove on array attributes sets `[]` instead of `unset` so relationship sync clears correctly
- `toArray()` now serializes `Arrayable` value objects (was commented out)

### Removed

- `errors` constructor parameter on `ScimErrorException` (replaced by `detail` string)

## [0.1.0] - 2026-03-19

### Added

- Initial release!
