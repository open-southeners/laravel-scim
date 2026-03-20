# Laravel SCIM

Integrate your Laravel application with the [SCIM v2](http://www.simplecloud.info/) specification for automated user and group provisioning.

SCIM (System for Cross-domain Identity Management) is the standard protocol used by identity providers like **Microsoft Entra ID** (Azure AD), **Okta**, **OneLogin**, and **JumpCloud** to automatically create, update, and delete users in your application.

## What this package does

* Exposes SCIM v2 endpoints under `/scim/v2/` with zero controller boilerplate
* Maps your Eloquent models to SCIM resources via simple PHP classes
* Handles validation, pagination, filtering, and PATCH operations per the spec
* Provides discovery endpoints (`ServiceProviderConfig`, `Schemas`, `ResourceTypes`) so identity providers can auto-configure

## Quick example

Define a schema that maps SCIM attributes to your User model:

```php
use OpenSoutheners\LaravelScim\Schemas\UserScimSchema;
use OpenSoutheners\LaravelScim\Attributes\ScimSchemaAttribute;
use OpenSoutheners\LaravelScim\Enums\ScimAttributeUniqueness;

readonly class MyUserSchema extends UserScimSchema
{
    public function __construct(
        #[ScimSchemaAttribute(modelAttribute: 'email', uniqueness: ScimAttributeUniqueness::Server)]
        string $userName,
        #[ScimSchemaAttribute(modelAttribute: 'name')]
        ?string $name = null,
    ) {
        $this->fill(func_get_args());
    }
}
```

Register it in a service provider:

```php
use OpenSoutheners\LaravelScim\Repository;

$repository = app(Repository::class);
$repository->add(User::class, MyUserSchema::class);
```

That's it. Your app now has a fully functional SCIM v2 API at `/scim/v2/Users`.

## Supported SCIM operations

| Operation | HTTP Method | Endpoint | Status |
|---|---|---|---|
| List resources | GET | `/{ResourceType}` | Supported |
| Get resource | GET | `/{ResourceType}/{id}` | Supported |
| Create resource | POST | `/{ResourceType}` | Supported |
| Replace resource | PUT | `/{ResourceType}/{id}` | Supported |
| Modify resource | PATCH | `/{ResourceType}/{id}` | Supported |
| Delete resource | DELETE | `/{ResourceType}/{id}` | Supported |
| Filter resources | GET | `/{ResourceType}?filter=...` | Supported |
| Pagination | GET | `/{ResourceType}?startIndex=1&count=10` | Supported |
| Service discovery | GET | `/ServiceProviderConfig` | Supported |
| Schema discovery | GET | `/Schemas` | Supported |
| Resource types | GET | `/ResourceTypes` | Supported |
| Bulk operations | POST | `/Bulk` | Not yet |
| ETag / Conditional | - | - | Not yet |
