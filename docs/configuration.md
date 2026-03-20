# Configuration

After publishing, the configuration file is at `config/scim.php`.

## Full config reference

```php
return [
    // Middleware applied to all SCIM routes.
    // Add your auth middleware here (e.g., 'auth:api' or 'auth:sanctum').
    'middleware' => [],

    // Route name prefix for all SCIM routes.
    'route_as' => 'scim.v2.',

    // Bulk operation limits (not yet implemented).
    'bulk' => [
        'maxPayloadSize' => 4194304, // bytes
        'maxOperations' => 10,
    ],

    // Filter query limits.
    'filter' => [
        'maxResults' => 100,
    ],

    // Database column name for the SCIM externalId attribute.
    // Override per-schema via ScimSchema::getExternalIdColumn().
    'external_id_column' => 'external_id',
];
```

## Middleware

You almost certainly want to protect SCIM endpoints with authentication. Add your auth guard:

```php
'middleware' => ['auth:sanctum'],
```

Or a custom middleware that validates bearer tokens from your identity provider:

```php
'middleware' => [App\Http\Middleware\ValidateScimToken::class],
```

## Custom externalId column

By default the package reads and writes `external_id` on your models. To use a different column globally:

```php
'external_id_column' => 'scim_external_id',
```

To customize per-schema, override the static method:

```php
readonly class MyUserSchema extends UserScimSchema
{
    public static function getExternalIdColumn(): string
    {
        return 'idp_id';
    }

    // ...
}
```

## Authentication schemes

Tell identity providers which authentication methods your SCIM endpoint supports. Call this in a service provider's `boot()` method:

```php
use OpenSoutheners\LaravelScim\Support\SCIM;
use OpenSoutheners\LaravelScim\Enums\ScimAuthenticationScheme;

SCIM::authenticationSchemes(ScimAuthenticationScheme::OAuthBearerToken);
```

Available schemes:

| Scheme | Description |
|---|---|
| `OAuthBearerToken` | OAuth 2.0 Bearer Token (RFC 6750) |
| `HttpBasic` | HTTP Basic Authentication (RFC 7617) |

This information is exposed in the `/scim/v2/ServiceProviderConfig` endpoint.
