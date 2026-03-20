# Enterprise Extensions

SCIM supports schema extensions that add extra attributes under a namespace. The most common is the Enterprise User extension, which adds attributes like `employeeNumber`, `department`, and `manager`.

## How it works

In SCIM JSON, extension attributes are nested under their URN:

```json
{
    "schemas": [
        "urn:ietf:params:scim:schemas:core:2.0:User",
        "urn:ietf:params:scim:schemas:extension:enterprise:2.0:User"
    ],
    "userName": "john@example.com",
    "name": "John Doe",
    "urn:ietf:params:scim:schemas:extension:enterprise:2.0:User": {
        "employeeNumber": "12345"
    }
}
```

## Using the built-in Enterprise User schema

The package includes `EnterpriseUserScimSchema` which extends `UserScimSchema` with the enterprise extension:

```php
use OpenSoutheners\LaravelScim\Schemas\EnterpriseUserScimSchema;
```

It adds the `employeeNumber` attribute under the enterprise extension URN.

## Creating your own extension

To add extension attributes to a schema, use the `extensionUrn` property on `ScimSchemaAttribute`:

```php
<?php

namespace App\SCIM;

use OpenSoutheners\LaravelScim\Attributes\ScimSchemaAttribute;
use OpenSoutheners\LaravelScim\Schemas\UserScimSchema;

readonly class MyEnterpriseUserSchema extends UserScimSchema
{
    public const EXTENSION_URN = 'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User';

    #[ScimSchemaAttribute(
        extensionUrn: self::EXTENSION_URN,
        modelAttribute: 'employee_number',
    )]
    public ?string $employeeNumber;

    #[ScimSchemaAttribute(
        extensionUrn: self::EXTENSION_URN,
        modelAttribute: 'department_name',
    )]
    public ?string $department;

    public function __construct(
        #[ScimSchemaAttribute(modelAttribute: 'email')]
        string $userName,
        #[ScimSchemaAttribute(modelAttribute: 'name')]
        ?string $name = null,
    ) {
        $this->fill(func_get_args());
    }

    public static function getSchemaUrns(): array
    {
        return array_merge(parent::getSchemaUrns(), [self::EXTENSION_URN]);
    }
}
```

### What happens

**On output** (`toArray()`): attributes with `extensionUrn` are grouped under their URN key:

```json
{
    "userName": "john@example.com",
    "urn:ietf:params:scim:schemas:extension:enterprise:2.0:User": {
        "employeeNumber": "12345",
        "department": "Engineering"
    },
    "schemas": ["urn:ietf:params:scim:schemas:core:2.0:User", "urn:ietf:params:scim:schemas:extension:enterprise:2.0:User"]
}
```

**On input**: the package automatically flattens extension-namespaced JSON before validation. An incoming request with nested extension attributes:

```json
{
    "userName": "john@example.com",
    "urn:ietf:params:scim:schemas:extension:enterprise:2.0:User": {
        "employeeNumber": "12345"
    }
}
```

Is flattened to:

```json
{
    "userName": "john@example.com",
    "employeeNumber": "12345"
}
```

This means your schema constructors and model attributes work with flat attribute names regardless of how the client sends the data.

## Custom extensions

You can define your own extension URN for application-specific attributes:

```php
#[ScimSchemaAttribute(
    extensionUrn: 'urn:example:schemas:extension:myapp:2.0:User',
    modelAttribute: 'custom_field',
)]
public ?string $myCustomField;
```

Remember to include your custom URN in `getSchemaUrns()` so it appears in the `schemas` array.
