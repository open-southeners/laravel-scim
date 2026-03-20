# Error Handling

The package automatically formats all errors on SCIM routes as SCIM-compliant JSON per [RFC 7644 Section 3.12](https://datatracker.ietf.org/doc/html/rfc7644#section-3.12).

## Error response format

All errors on SCIM routes return JSON in this format:

```json
{
    "schemas": ["urn:ietf:params:scim:api:messages:2.0:Error"],
    "status": "400",
    "scimType": "invalidValue",
    "detail": "The userName field is required."
}
```

## Automatic error conversion

The package intercepts these exception types on SCIM routes and converts them:

| Exception | HTTP Status | scimType |
|---|---|---|
| `ValidationException` | 400 | `invalidValue` |
| `AuthenticationException` | 401 | - |
| `HttpException` (404) | 404 | - |
| `HttpException` (other) | varies | - |

## Setup with Laravel 11+

If you're using Laravel 11's `bootstrap/app.php` exception handling, register the SCIM error handler:

```php
// bootstrap/app.php
use OpenSoutheners\LaravelScim\Support\SCIM;

return Application::configure(basePath: dirname(__DIR__))
    ->withExceptions(function (Exceptions $exceptions) {
        SCIM::integrate($exceptions);
    })
    ->create();
```

{% hint style="info" %}
If you're using Laravel 10 with the `ServiceProvider`-based exception handler, the package registers error handling automatically. No extra setup needed.
{% endhint %}

## Throwing SCIM errors manually

Use `ScimErrorException` to throw spec-compliant errors from your custom code:

```php
use OpenSoutheners\LaravelScim\Exceptions\ScimErrorException;
use OpenSoutheners\LaravelScim\Enums\ScimBadRequestErrorType;

throw new ScimErrorException(
    type: ScimBadRequestErrorType::Uniqueness,
    detail: 'A user with this email already exists.',
);
```

### Available error types

| Type | scimType value | Description |
|---|---|---|
| `InvalidFilter` | `invalidFilter` | Filter syntax is invalid |
| `TooMany` | `tooMany` | Too many results |
| `Uniqueness` | `uniqueness` | Uniqueness constraint violated |
| `Mutability` | `mutability` | Attempted to modify read-only attribute |
| `InvalidSyntax` | `invalidSyntax` | Request body is malformed |
| `InvalidPath` | `invalidPath` | Path in PATCH operation is invalid |
| `NoTarget` | `noTarget` | Target resource not found for PATCH |
| `InvalidValue` | `invalidValue` | Value doesn't match expected type |
| `InvalidVersion` | `invalidVers` | Version mismatch |
| `Sensitive` | `sensitive` | Operation on sensitive attribute |

## Non-SCIM routes

Error formatting only applies to routes with the `scim.v2.*` name prefix. Your regular API and web routes are not affected.
