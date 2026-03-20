# Defining Schemas

A schema class is the bridge between SCIM JSON and your Eloquent model. It declares which SCIM attributes exist, how they map to database columns, and what validation rules apply.

## Base schemas

The package provides two base schemas you can extend:

| Base class | SCIM URN | Built-in properties |
|---|---|---|
| `UserScimSchema` | `urn:ietf:params:scim:schemas:core:2.0:User` | `userName`, `name`, `emails`, `active`, `roles` |
| `GroupScimSchema` | `urn:ietf:params:scim:schemas:core:2.0:Group` | `displayName`, `members` |

You extend a base schema and define a constructor that maps your specific model attributes.

## User schema example

```php
<?php

namespace App\SCIM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use OpenSoutheners\LaravelScim\Attributes\ScimSchemaAttribute;
use OpenSoutheners\LaravelScim\Enums\ScimAttributeUniqueness;
use OpenSoutheners\LaravelScim\Schemas\UserScimSchema;

readonly class MyUserSchema extends UserScimSchema
{
    public function __construct(
        #[ScimSchemaAttribute(
            modelAttribute: 'email',
            uniqueness: ScimAttributeUniqueness::Server,
        )]
        string $userName,

        #[ScimSchemaAttribute(modelAttribute: 'name')]
        ?string $name = null,
    ) {
        $this->fill(func_get_args());
    }

    public function toModel(?Model $model = null): Model
    {
        $model = parent::toModel($model);

        // Set a random password for new users created via SCIM
        if (! $model->exists && ! $model->password) {
            $model->password = Hash::make(Str::random(32));
        }

        return $model;
    }
}
```

### What's happening here

1. **Constructor parameters** = SCIM attributes. The parameter name becomes the SCIM attribute name in JSON.
2. **`#[ScimSchemaAttribute]`** maps each SCIM attribute to a model column and configures validation.
3. **`$this->fill(func_get_args())`** assigns values and coerces types (e.g., arrays of raw data into value objects).
4. **`toModel()`** override lets you add custom logic when converting a SCIM request into an Eloquent model.

## Group schema example

```php
<?php

namespace App\SCIM;

use OpenSoutheners\LaravelScim\Attributes\ScimSchemaAttribute;
use OpenSoutheners\LaravelScim\GroupMember;
use OpenSoutheners\LaravelScim\Schemas\GroupScimSchema;

readonly class MyGroupSchema extends GroupScimSchema
{
    public function __construct(
        #[ScimSchemaAttribute(modelAttribute: 'name')]
        string $displayName,

        /** @var null|array<GroupMember> */
        #[ScimSchemaAttribute(modelRelationship: 'members', multiValued: true)]
        ?array $members = null,
    ) {
        $this->fill(func_get_args());
    }
}
```

The `modelRelationship` option tells the package to use the `members()` Eloquent relationship for reading and syncing group membership.

## ScimSchemaAttribute reference

| Property | Type | Default | Description |
|---|---|---|---|
| `description` | `string` | `''` | Human-readable description exposed in schema discovery |
| `modelAttribute` | `?string` | `null` | Database column name. Falls back to the parameter name |
| `mutability` | `ScimAttributeMutability` | `ReadWrite` | `ReadWrite`, `ReadOnly`, `WriteOnly`, or `Immutable` |
| `uniqueness` | `ScimAttributeUniqueness` | `None` | `None`, `Server`, or `Global`. Server adds a unique validation rule |
| `returned` | `ScimAttributeReturned` | `Default` | `Default`, `Always`, `Never`, or `Excluded` |
| `multiValued` | `bool` | `false` | Whether the attribute holds an array of values |
| `caseExact` | `bool` | `false` | Whether comparisons are case-sensitive |
| `modelRelationship` | `?string` | `null` | Eloquent relationship method name (for BelongsToMany) |
| `relationshipValueKey` | `?string` | `null` | Column to use as the `value` key in relationship data |
| `extensionUrn` | `?string` | `null` | Extension schema URN for namespace grouping (see [Enterprise Extensions](enterprise-extensions.md)) |

## Building a schema from scratch

If neither `UserScimSchema` nor `GroupScimSchema` fits your resource, extend `ScimSchema` directly:

```php
<?php

namespace App\SCIM;

use Illuminate\Database\Eloquent\Builder;
use OpenSoutheners\LaravelScim\Attributes\ScimSchemaAttribute;
use OpenSoutheners\LaravelScim\ScimSchema;

readonly class DeviceSchema extends ScimSchema
{
    public function __construct(
        #[ScimSchemaAttribute(modelAttribute: 'serial_number')]
        string $serialNumber,

        #[ScimSchemaAttribute(modelAttribute: 'display_name')]
        ?string $displayName = null,
    ) {
        $this->fill(func_get_args());
    }

    public static function getSchemaUrns(): array
    {
        return ['urn:example:schemas:2.0:Device'];
    }

    public static function getSchemaName(): string
    {
        return 'Device';
    }

    public static function getSchemaDescription(): string
    {
        return 'A provisioned device';
    }

    public static function query(Builder $query): void
    {
        // Add default scoping for all list/get queries
        $query->where('provisioned', true);
    }
}
```

## Query scoping

The static `query()` method is called on every list and get request. Use it to add default filters, eager loads, or tenant scoping:

```php
public static function query(Builder $query): void
{
    $query->where('tenant_id', auth()->user()->tenant_id)
          ->with('department');
}
```

## Customizing model creation

Override `toModel()` to add logic when a SCIM resource is converted to an Eloquent model:

```php
public function toModel(?Model $model = null): Model
{
    $model = parent::toModel($model);

    if (! $model->exists) {
        $model->role = 'member';
        $model->password = Hash::make(Str::random(32));
    }

    return $model;
}
```

## Type coercion

The `fill()` method automatically coerces values based on parent property types. For example, if the parent schema declares:

```php
/** @var null|array<UserEmail> */
public ?array $emails;
```

And the incoming JSON contains:

```json
{
    "emails": [
        {"value": "john@example.com", "type": "work", "primary": true}
    ]
}
```

The `fill()` method will automatically construct `UserEmail` objects from the raw arrays.

## Value objects

The package includes value objects for multi-valued SCIM attributes:

| Class | Properties |
|---|---|
| `UserEmail` | `value`, `type`, `primary` |
| `UserRole` | `value`, `type`, `primary` |
| `GroupMember` | `value`, `display`, `type`, `ref` |
