# Events

The package dispatches Laravel events during SCIM resource lifecycle operations. Use these to hook into provisioning actions without modifying the core code.

## Available events

### Create resource (POST)

| Event | When |
|---|---|
| `scim.model.saving` | Before saving (create or update) |
| `scim.model.creating` | Before creating a new model |
| `scim.model.created` | After the model is created |
| `scim.model.saved` | After saving (create or update) |

### Update resource (PUT/PATCH)

| Event | When |
|---|---|
| `scim.model.saving` | Before saving |
| `scim.model.updating` | Before updating an existing model |
| `scim.model.updated` | After the model is updated |
| `scim.model.saved` | After saving |

### Delete resource (DELETE)

| Event | When |
|---|---|
| `scim.model.deleting` | Before deleting the model |
| `scim.model.deleted` | After the model is deleted |

## Listening to events

Register listeners in a service provider:

```php
use Illuminate\Support\Facades\Event;

Event::listen('scim.model.creating', function ($model) {
    // Set defaults for SCIM-provisioned users
    $model->source = 'scim';
});

Event::listen('scim.model.created', function ($model) {
    // Send welcome notification
    if ($model instanceof User) {
        $model->notify(new WelcomeNotification());
    }
});

Event::listen('scim.model.deleting', function ($model) {
    // Soft-delete instead of hard-delete
    // Or run cleanup logic
    Log::info("SCIM deleting {$model->getTable()} #{$model->getKey()}");
});
```

## Execution order

For a create operation:

```
Gate check → Validate request → Create schema
→ scim.model.saving → scim.model.creating
→ Model saved to database
→ scim.model.created → scim.model.saved
→ Sync relationships → Return response
```

For an update:

```
Gate check → Validate request → Create schema
→ scim.model.saving → scim.model.updating
→ Model saved to database
→ scim.model.updated → scim.model.saved
→ Sync relationships → Return response
```
