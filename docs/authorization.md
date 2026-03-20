# Authorization

The package uses Laravel's Gate system for authorization. Every SCIM operation checks a gate before proceeding.

## Gate abilities

| Operation | Gate ability | Extra arguments |
|---|---|---|
| List resources | `scim.{table}.viewAny` | - |
| Get resource | `scim.{table}.view` | `[$id]` |
| Create resource | `scim.{table}.create` | - |
| Update resource | `scim.{table}.update` | `[$id]` |
| Delete resource | `scim.{table}.delete` | `[$id]` |

The `{table}` is the model's database table name. For example, a `User` model with table `users` produces abilities like `scim.users.create`.

## Defining gates

In your `AuthServiceProvider` or `AppServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

// Allow all SCIM operations for any authenticated user
Gate::before(function ($user, $ability) {
    if (str_starts_with($ability, 'scim.')) {
        return true;
    }
});
```

Or define specific abilities:

```php
Gate::define('scim.users.viewAny', function ($user) {
    return $user->hasRole('scim-admin');
});

Gate::define('scim.users.create', function ($user) {
    return $user->hasRole('scim-admin');
});

Gate::define('scim.groups.viewAny', function ($user) {
    return $user->hasRole('scim-admin');
});

// ... etc
```

## Authorization order

Authorization is checked **before** request validation and model construction. This means:

1. Request comes in
2. Gate check runs (against the authenticated user)
3. If denied, a 403 response is returned immediately
4. If allowed, request validation and schema processing proceed

This avoids wasted work for unauthorized requests.

## Authentication

The package does not handle authentication itself. You should configure auth middleware in `config/scim.php`:

```php
'middleware' => ['auth:sanctum'],
```

The authenticated user from your middleware is the one passed to Gate checks.

{% hint style="warning" %}
If no middleware is configured and no user is authenticated, Gate checks will receive `null` as the user, which typically denies all operations. Always configure authentication middleware for SCIM endpoints.
{% endhint %}
