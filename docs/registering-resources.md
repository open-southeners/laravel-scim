# Registering Resources

After defining your schemas, register them in a service provider to make them available as SCIM endpoints.

## Basic registration

In your `AppServiceProvider` (or a dedicated `ScimServiceProvider`):

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenSoutheners\LaravelScim\Repository;
use OpenSoutheners\LaravelScim\Support\SCIM;
use OpenSoutheners\LaravelScim\Enums\ScimAuthenticationScheme;
use App\Models\User;
use App\Models\Group;
use App\SCIM\MyUserSchema;
use App\SCIM\MyGroupSchema;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Configure authentication scheme
        SCIM::authenticationSchemes(ScimAuthenticationScheme::OAuthBearerToken);

        // Register SCIM resources
        $repository = app(Repository::class);
        $repository->add(User::class, MyUserSchema::class);
        $repository->add(Group::class, MyGroupSchema::class);
    }
}
```

## What registration does

When you call `$repository->add(User::class, MyUserSchema::class)`:

1. The model is associated with the schema under a SCIM URN (auto-generated from the model name)
2. A route slug is auto-registered (e.g., `Users` for `User::class`, `Groups` for `Group::class`)
3. The following endpoints become active:

| Method | URL | Description |
|---|---|---|
| GET | `/scim/v2/Users` | List users |
| GET | `/scim/v2/Users/{id}` | Get single user |
| POST | `/scim/v2/Users` | Create user |
| PUT | `/scim/v2/Users/{id}` | Replace user |
| PATCH | `/scim/v2/Users/{id}` | Modify user |
| DELETE | `/scim/v2/Users/{id}` | Delete user |

## Route resolution

The route slug is derived from the model class name, pluralized. `User` becomes `Users`, `Group` becomes `Groups`.

The resolution is **case-insensitive**, so `/scim/v2/users` and `/scim/v2/Users` both work.

## Custom URN

If you need a custom SCIM URN for a resource:

```php
$repository->add(
    User::class,
    MyUserSchema::class,
    'urn:example:schemas:custom:2.0:Employee'
);
```

## Model requirements

Your Eloquent model needs:

1. An `external_id` column (or your configured column name) that is fillable
2. For group-like resources with members: a `BelongsToMany` relationship

```php
// app/Models/Group.php
class Group extends Model
{
    protected $fillable = ['name', 'external_id'];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
```

## Custom route binding

If your model uses UUIDs or another non-standard key, implement `resolveScimRouteBinding`:

```php
class User extends Authenticatable
{
    public function resolveScimRouteBinding(string $value): ?static
    {
        return static::where('uuid', $value)->first();
    }
}
```

When this method exists, the package uses it instead of Laravel's default `resolveRouteBinding`.
