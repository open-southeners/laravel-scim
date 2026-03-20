# Testing

Tips for testing your SCIM integration in your Laravel application.

## Test setup

In your test base class, you need to:

1. Allow SCIM gate checks
2. Authenticate a user (since Gate needs a user context)

```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Allow all SCIM gate checks in tests
        Gate::before(fn ($user, $ability) => true);

        // Authenticate a user for Gate context
        $this->actingAs(User::factory()->create());
    }
}
```

## Testing user provisioning

```php
public function test_scim_creates_user(): void
{
    $response = $this->postJson('/scim/v2/Users', [
        'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:User'],
        'userName' => 'jane@example.com',
        'name' => 'Jane Doe',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
        'name' => 'Jane Doe',
    ]);
}
```

## Testing user updates

```php
public function test_scim_updates_user_via_put(): void
{
    $user = User::factory()->create([
        'email' => 'old@example.com',
        'name' => 'Old Name',
    ]);

    $response = $this->putJson("/scim/v2/Users/{$user->id}", [
        'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:User'],
        'userName' => 'new@example.com',
        'name' => 'New Name',
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('users', [
        'email' => 'new@example.com',
    ]);
}
```

## Testing PATCH operations

```php
public function test_scim_patches_group_members(): void
{
    $user = User::factory()->create(['name' => 'Alice']);
    $group = Group::factory()->create(['name' => 'Engineering']);

    $response = $this->patchJson("/scim/v2/Groups/{$group->id}", [
        'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
        'Operations' => [
            [
                'op' => 'add',
                'path' => 'members',
                'value' => [
                    ['value' => (string) $user->id, 'display' => 'Alice'],
                ],
            ],
        ],
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('group_user', [
        'user_id' => $user->id,
        'group_id' => $group->id,
    ]);
}
```

## Testing list with filters

```php
public function test_scim_filters_users(): void
{
    User::factory()->create(['email' => 'alice@example.com']);
    User::factory()->create(['email' => 'bob@example.com']);

    $response = $this->getJson(
        '/scim/v2/Users?filter=' . urlencode('userName eq "alice@example.com"')
    );

    $response->assertOk();
    $response->assertJsonCount(1, 'Resources');
}
```

## Testing error responses

```php
public function test_scim_validation_error_format(): void
{
    $response = $this->postJson('/scim/v2/Users', [
        'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:User'],
        // Missing required userName
    ]);

    $response->assertStatus(400);

    $response->assertJsonFragment([
        'schemas' => ['urn:ietf:params:scim:api:messages:2.0:Error'],
        'status' => '400',
        'scimType' => 'invalidValue',
    ]);
}
```

## Testing with named routes

You can also use named routes:

```php
$this->getJson(route('scim.v2.SchemaActions.index', ['schema' => 'Users']));
$this->getJson(route('scim.v2.SchemaActions.show', ['schema' => 'Users', 'id' => $user->id]));
$this->postJson(route('scim.v2.SchemaActions.store', ['schema' => 'Users']), $data);
$this->putJson(route('scim.v2.SchemaActions.update', ['schema' => 'Users', 'id' => $user->id]), $data);
$this->deleteJson(route('scim.v2.SchemaActions.destroy', ['schema' => 'Users', 'id' => $user->id]));
```

## Route names reference

| Route name | Description |
|---|---|
| `scim.v2.ServiceProviderConfig` | Service provider config |
| `scim.v2.ResourceTypes` | Resource types list |
| `scim.v2.Schemas.index` | All schemas |
| `scim.v2.Schemas.show` | Single schema |
| `scim.v2.SchemaActions.index` | List resources |
| `scim.v2.SchemaActions.show` | Get single resource |
| `scim.v2.SchemaActions.store` | Create resource |
| `scim.v2.SchemaActions.update` | Update resource (PUT/PATCH) |
| `scim.v2.SchemaActions.destroy` | Delete resource |
