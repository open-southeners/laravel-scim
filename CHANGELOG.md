# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
